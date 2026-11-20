<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\Assessment;
use App\Models\Module;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    private function teacher()
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($teacher, 403);
        return $teacher;
    }

    private function student(): Student
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403);
        return $student;
    }

    /** An individual recovery can swap the exam's activities for one selected activity. */
    private function assignedActivities(Assessment $assessment, Student $student)
    {
        $pivot = $student->assessments()->whereKey($assessment->id)->firstOrFail()->pivot;
        if ($pivot->attempt_number > 1 && $pivot->recovery_activity_id) {
            return Activity::with('module')->whereKey($pivot->recovery_activity_id)->get();
        }
        return $assessment->activities;
    }

    private function currentResults(Assessment $assessment, Student $student)
    {
        $pivot = $student->assessments()->whereKey($assessment->id)->firstOrFail()->pivot;
        return ActivityResult::where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)
            ->where('attempt_number', $pivot->attempt_number)->get();
    }

    private function mayPlay(Assessment $assessment, Student $student, Activity $activity): void
    {
        abort_unless($assessment->ativa, 403, 'Esta avaliação foi finalizada pelo professor.');
        $pivot = $student->assessments()->whereKey($assessment->id)->firstOrFail()->pivot;
        abort_if($assessment->prazo && now()->greaterThan($assessment->prazo) && $pivot->attempt_number <= 1,
            403, 'O prazo desta avaliação terminou. Solicite a reabertura ao professor.');
        abort_unless($this->assignedActivities($assessment, $student)->contains('id', $activity->id),
            403, 'Esta atividade não faz parte da sua tentativa atual.');
        abort_if(ActivityResult::where('assessment_id', $assessment->id)
            ->where('student_id', $student->id)->where('activity_id', $activity->id)
            ->where('attempt_number', $pivot->attempt_number)->exists(),
            403, 'Esta atividade já foi concluída. Somente o professor pode liberar uma recuperação.');
    }

    public function teacherHome()
    {
        $teacher = $this->teacher();
        $classes = $teacher->classes()->with('students.results')->orderBy('nome')->get();
        $allStudents = $teacher->students()->with('results')->get();
        $classMeans = $classes->map(function ($class) {
            $scores = $class->students->flatMap(fn ($student) => $student->results->pluck('score'));
            return ['class' => $class, 'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null];
        });
        $allScores = $allStudents->flatMap(fn ($student) => $student->results->pluck('score'));
        $teacherDashboard = [
            'classes' => $classes->count(), 'students' => $allStudents->count(),
            'assessments' => $teacher->assessments()->count(),
            'average' => $allScores->isNotEmpty() ? round($allScores->avg(), 2) : null,
            'classMeans' => $classMeans,
        ];
        return view('homeUser', compact('teacherDashboard'));
    }

    public function teacherIndex()
    {
        $teacher = $this->teacher();
        foreach (['libras', 'portugues'] as $subject) {
            $module = Module::firstOrCreate(['tema' => $subject, 'numeroModulo' => 1]);
            Activity::firstOrCreate(['nomeMateria' => $subject, 'numeroAtividade' => 1, 'fk_modulos_id' => $module->id],
                ['nome' => $subject === 'libras' ? 'Alfabeto em Libras' : 'Letras e palavras']);
        }
        $assessments = $teacher->assessments()->with(['activities', 'students'])->latest()->get();
        $students = $teacher->students()->with('class')->orderBy('nome')->get();
        $classes = $teacher->classes()->withCount('students')->orderBy('nome')->get();
        $modules = Module::with(['activities' => fn ($q) => $q->orderBy('numeroAtividade')])
            ->orderBy('tema')->orderBy('numeroModulo')->get();
        $availableActivityCount = $modules->flatMap->activities->filter(fn ($a) => $a->numeroAtividade == 1 && in_array(strtolower($a->nomeMateria), ['libras','portugues']) && $a->module?->numeroModulo == 1)->count();
        return view('teacher.assessments', compact('assessments', 'students', 'classes', 'modules', 'availableActivityCount'));
    }

    public function store(Request $request)
    {
        $teacher = $this->teacher();
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:120'], 'descricao' => ['nullable', 'string', 'max:2000'],
            'prazo_data' => ['nullable', 'required_with:prazo_hora', 'date_format:Y-m-d'],
            'prazo_hora' => ['nullable', 'required_with:prazo_data', 'date_format:H:i'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*' => ['integer', 'distinct', 'exists:activities,id'],
            'assignment_mode' => ['required', 'in:class,students'],
            'class_id' => ['required_if:assignment_mode,class', 'nullable', 'integer'],
            'students' => ['required_if:assignment_mode,students', 'array'],
            'students.*' => ['integer', 'distinct'],
        ]);
        // Only games with a working view/result collector may be assigned.
        $chosenActivities = Activity::with('module')->whereIn('id', $data['activities'])->get();
        if ($chosenActivities->count() !== count($data['activities']) ||
            $chosenActivities->contains(fn ($activity) => $activity->numeroAtividade != 1 ||
                $activity->module?->numeroModulo != 1 ||
                !in_array(strtolower($activity->nomeMateria), ['libras', 'portugues']))) {
            throw ValidationException::withMessages(['activities' => 'Escolha somente atividades com jogos implementados.']);
        }
        if ($data['assignment_mode'] === 'class') {
            $class = $teacher->classes()->findOrFail($data['class_id']);
            $studentIds = $class->students()->pluck('id');
        } else {
            $studentIds = $teacher->students()->whereIn('id', $data['students'] ?? [])->pluck('id');
            if ($studentIds->count() !== count($data['students'] ?? [])) {
                throw ValidationException::withMessages(['students' => 'Selecione somente alunos do seu cadastro.']);
            }
        }
        if ($studentIds->isEmpty()) {
            throw ValidationException::withMessages(['students' => 'A turma ou seleção precisa ter pelo menos um aluno.']);
        }
        $deadline = null;
        if (!empty($data['prazo_data']) && !empty($data['prazo_hora'])) {
            $deadline = Carbon::createFromFormat('!Y-m-d H:i', $data['prazo_data'].' '.$data['prazo_hora']);
            if ($deadline->isPast()) throw ValidationException::withMessages(['prazo_data' => 'Escolha um prazo futuro.']);
        }
        DB::transaction(function () use ($teacher, $data, $studentIds, $deadline) {
            $assessment = $teacher->assessments()->create([
                'titulo' => $data['titulo'], 'descricao' => $data['descricao'] ?? null,
                'prazo' => $deadline, 'ativa' => true,
            ]);
            foreach ($data['activities'] as $i => $activityId) {
                $assessment->activities()->attach($activityId, ['ordem' => $i + 1]);
            }
            $assessment->students()->attach($studentIds);
        });
        return redirect()->route('teacher.assessments')->with('success', 'Avaliação criada e atribuída com sucesso.');
    }

    public function status(Request $request, int $id)
    {
        $assessment = $this->teacher()->assessments()->findOrFail($id);
        $data = $request->validate(['ativa' => ['required', 'boolean']]);
        $assessment->update(['ativa' => (bool) $data['ativa']]);
        return back()->with('success', $assessment->ativa ? 'Avaliação reaberta.' : 'Avaliação finalizada para todos os alunos.');
    }

    public function destroy(int $id)
    {
        $assessment = $this->teacher()->assessments()->findOrFail($id);
        // Foreign keys remove assignments and records so the deleted assessment vanishes for everyone.
        $assessment->delete();
        return redirect()->route('teacher.assessments')->with('success', 'Avaliação excluída. As atribuições e notas associadas foram removidas.');
    }

    public function reopen(Request $request, int $assessmentId, int $studentId)
    {
        $assessment = $this->teacher()->assessments()->with('activities')->findOrFail($assessmentId);
        if (!$assessment->ativa) return back()->withErrors(['reopen' => 'Reabra a avaliação antes de liberar uma recuperação.']);
        $student = $assessment->students()->whereKey($studentId)->firstOrFail();
        $data = $request->validate(['recovery_activity_id' => ['nullable', 'integer', 'exists:activities,id']]);
        if (!empty($data['recovery_activity_id'])) {
            $alternate = Activity::with('module')->findOrFail($data['recovery_activity_id']);
            if ($alternate->numeroAtividade != 1 || $alternate->module?->numeroModulo != 1 ||
                !in_array(strtolower($alternate->nomeMateria), ['libras', 'portugues'])) {
                throw ValidationException::withMessages(['recovery_activity_id' => 'Esta atividade ainda não tem um jogo funcional.']);
            }
        }
        if ($this->currentResults($assessment, $student)->isEmpty()) {
            return back()->withErrors(['reopen' => 'O aluno ainda não realizou esta tentativa.']);
        }
        $assessment->students()->updateExistingPivot($student->id, [
            'attempt_number' => $student->pivot->attempt_number + 1,
            'recovery_activity_id' => $data['recovery_activity_id'] ?? null,
        ]);
        return back()->with('success', 'Recuperação liberada somente para '.$student->nome.'. A tentativa anterior foi preservada.');
    }

    public function studentIndex()
    {
        $student = $this->student();
        $assessments = $student->assessments()->with('activities')->orderByDesc('assessments.created_at')->get();
        $latest = ActivityResult::where('student_id', $student->id)->whereNotNull('assessment_id')->get()
            ->groupBy(fn ($r) => $r->assessment_id.'-'.$r->attempt_number);
        return view('students.assessments', compact('assessments', 'latest'));
    }

    public function learningResults()
    {
        $student = Auth::guard('student')->user();
        $user = Auth::guard('web')->user();
        abort_unless($student || $user, 403);
        $query = ActivityResult::query();
        if ($student) $query->where('student_id', $student->id);
        else $query->where('user_id', $user->id);
        $all = $query->with(['activity', 'assessment'])->latest('completed_at')->get();
        $results = $all->whereNull('assessment_id');
        $assessmentResults = $student ? $all->whereNotNull('assessment_id') : collect();
        return view('students.results', compact('results', 'assessmentResults'));
    }

    public function start(int $id)
    {
        $student = $this->student();
        $assessment = $student->assessments()->with('activities.module')->findOrFail($id);
        $activities = $this->assignedActivities($assessment, $student);
        $doneIds = $this->currentResults($assessment, $student)->pluck('activity_id')->all();
        $finished = $activities->isNotEmpty() && $activities->every(fn ($a) => in_array($a->id, $doneIds));
        $locked = !$assessment->ativa || ($assessment->prazo && now()->greaterThan($assessment->prazo) && $assessment->pivot->attempt_number <= 1);
        return view('students.assessment-detail', compact('assessment', 'activities', 'doneIds', 'finished', 'locked'));
    }

    public function launchActivity(int $assessmentId, int $activityId)
    {
        $student = $this->student();
        $assessment = $student->assessments()->with('activities')->findOrFail($assessmentId);
        $activity = Activity::findOrFail($activityId);
        $this->mayPlay($assessment, $student, $activity);
        return app(ProgressController::class)->startAssessmentActivity($activity, $assessment->id);
    }

    public function assertActivityAvailable(int $assessmentId, Activity $activity): void
    {
        $student = $this->student();
        $assessment = $student->assessments()->with('activities')->findOrFail($assessmentId);
        $this->mayPlay($assessment, $student, $activity);
    }

    public function teacherPerformance(Request $request)
    {
        $teacher = $this->teacher();
        $classes = $teacher->classes()->orderBy('nome')->get();
        $selectedClass = null;
        if ($request->filled('class_id')) $selectedClass = $teacher->classes()->findOrFail((int) $request->query('class_id'));
        $students = $teacher->students()->with(['results.activity', 'class'])
            ->when($selectedClass, fn ($q) => $q->where('fk_turmas_id', $selectedClass->id))
            ->orderBy('nome')->get();
        $grades = $students->flatMap(fn ($s) => $s->results->pluck('score'));
        $mean = $grades->isNotEmpty() ? round($grades->avg(), 2) : null;
        return view('teacher.performance', compact('students', 'classes', 'selectedClass', 'mean'));
    }

    public function studentPerformance(int $id)
    {
        $teacher = $this->teacher();
        $student = $teacher->students()->with(['class', 'progresses.activity', 'assessments', 'results.activity', 'results.assessment'])->findOrFail($id);
        $results = $student->results->sortByDesc('completed_at');
        $freeResults = $results->whereNull('assessment_id');
        $assessmentResults = $results->whereNotNull('assessment_id');
        $mean = $results->count() ? round($results->avg('score'), 2) : null;
        return view('teacher.student-performance', compact('student', 'results', 'freeResults', 'assessmentResults', 'mean'));
    }

    public function record(Request $request)
    {
        $data = $request->validate([
            'materia' => ['required', 'string', 'max:15'], 'numeroAtividade' => ['required', 'integer', 'min:1'],
            'correct' => ['required', 'integer', 'min:0'], 'total' => ['required', 'integer', 'min:1'],
            'assessment_id' => ['nullable', 'integer', 'exists:assessments,id'],
        ]);
        if ($data['correct'] > $data['total']) abort(422, 'Acertos não podem exceder o total.');
        $activity = Activity::where('nomeMateria', $data['materia'])->where('numeroAtividade', $data['numeroAtividade'])->firstOrFail();
        $result = [
            'activity_id' => $activity->id, 'correct' => $data['correct'], 'total' => $data['total'],
            'errors' => $data['total'] - $data['correct'],
            'score' => round(($data['correct'] / $data['total']) * 10, 2), 'completed_at' => now(),
        ];
        if ($student = Auth::guard('student')->user()) $result['student_id'] = $student->id;
        elseif ($user = Auth::guard('web')->user()) $result['user_id'] = $user->id;
        else abort(403);
        if (!empty($data['assessment_id'])) {
            abort_unless(isset($student), 403);
            $assessment = $student->assessments()->with('activities')->findOrFail($data['assessment_id']);
            $this->mayPlay($assessment, $student, $activity);
            $result['assessment_id'] = $assessment->id;
            $result['attempt_number'] = $assessment->pivot->attempt_number;
            $result['submission_key'] = implode('-', [$assessment->id, $student->id, $activity->id, $assessment->pivot->attempt_number]);
        }
        try {
            ActivityResult::create($result);
        } catch (QueryException $exception) {
            // An atomic unique submission key also protects against two simultaneous requests.
            if (!empty($result['submission_key']) && ActivityResult::where('submission_key', $result['submission_key'])->exists()) {
                abort(409, 'Esta atividade já foi enviada nesta tentativa.');
            }
            throw $exception;
        }
        return response()->json(['saved' => true, 'score' => $result['score'], 'assessment' => !empty($data['assessment_id'])]);
    }

    public function congratulations(Request $request)
    {
        $assessment = null;
        $finished = false;
        $remaining = 0;
        if ($request->filled('assessment_id') && ($student = Auth::guard('student')->user())) {
            $assessment = $student->assessments()->with('activities')->findOrFail($request->integer('assessment_id'));
            $activities = $this->assignedActivities($assessment, $student);
            $done = $this->currentResults($assessment, $student)->pluck('activity_id')->all();
            $remaining = $activities->filter(fn ($activity) => !in_array($activity->id, $done))->count();
            $finished = $activities->isNotEmpty() && $remaining === 0;
        }
        return view('students.activities.firstActivity.congratulations', compact('assessment', 'finished', 'remaining'));
    }

    public function teacherResults(int $id)
    {
        $teacher = $this->teacher();
        $assessment = $teacher->assessments()->with('activities')->findOrFail($id);
        $students = $assessment->students()->with('class')->orderBy('nome')->get();
        $records = ActivityResult::where('assessment_id', $assessment->id)->with('activity')->get()->groupBy('student_id');
        $report = [];
        foreach ($students as $student) {
            $attempt = $student->pivot->attempt_number;
            $items = $records->get($student->id, collect())->where('attempt_number', $attempt)
                ->groupBy('activity_id')->map(fn ($attempts) => $attempts->sortByDesc('completed_at')->first())->values();
            $expected = $this->assignedActivities($assessment, $student)->count();
            $report[] = [
                'student' => $student, 'items' => $items, 'score' => $items->isNotEmpty() ? round($items->avg('score'), 2) : null,
                'completed' => $items->count(), 'totalActivities' => $expected, 'attempt' => $attempt,
            ];
        }
        $completeScores = collect($report)->filter(fn ($r) => $r['totalActivities'] > 0 && $r['completed'] >= $r['totalActivities'])->pluck('score');
        $classAverage = $completeScores->isNotEmpty() ? round($completeScores->avg(), 2) : null;
        $completedStudents = $completeScores->count();
        $activities = Activity::with('module')->orderBy('nomeMateria')->orderBy('numeroAtividade')->get()->filter(fn ($a) => $a->numeroAtividade == 1 && $a->module?->numeroModulo == 1 && in_array(strtolower($a->nomeMateria), ['libras','portugues']));
        return view('teacher.assessment-results', compact('assessment', 'report', 'classAverage', 'completedStudents', 'activities'));
    }
}
