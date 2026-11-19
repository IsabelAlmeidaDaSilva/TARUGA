<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Student;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    private array $statusToViewMap = [
        0 => 'students.activities.firstActivity.firstScreen',
        15 => 'students.activities.firstActivity.secondScreen',
        30 => 'students.activities.firstActivity.thirdScreen',
        50 => 'students.activities.firstActivity.preActivity',
        100 => 'students.activities.firstActivity.activity',
    ];

    private array $statusToViewMapPt = [
        0 => 'students.activities.firstActivityPort.contentPart1',
        15 => 'students.activities.firstActivityPort.contentPart2',
        30 => 'students.activities.firstActivityPort.gamePart1',
        50 => 'students.activities.firstActivityPort.gamePart2',
        75 => 'students.activities.firstActivityPort.activityPart1',
        100 => 'students.activities.firstActivityPort.activityPart2',
    ];

    private function learner(): Student|User
    {
        if ($student = Auth::guard('student')->user()) {
            return $student;
        }

        if ($user = Auth::guard('web')->user()) {
            return $user;
        }

        abort(403, 'É necessário entrar como aluno ou usuário para acessar as atividades.');
    }

    private function activityFor(string $materia, int $numeroAtividade): Activity
    {
        $module = Module::firstOrCreate(
            ['tema' => $materia, 'numeroModulo' => 1],
            []
        );

        return Activity::firstOrCreate(
            [
                'nomeMateria' => $materia,
                'numeroAtividade' => $numeroAtividade,
                'fk_modulos_id' => $module->id,
            ],
            ['nome' => 'Atividade '.$numeroAtividade]
        );
    }

    private function findProgress(Student|User $learner, string $materia, int $numeroAtividade, bool $create = false): Progress|UserProgress|null
    {
        $activity = $this->activityFor($materia, $numeroAtividade);

        if ($learner instanceof Student) {
            $criteria = [
                'fk_estudantes_id' => $learner->id,
                'fk_atividades_id' => $activity->id,
            ];

            return $create
                ? Progress::firstOrCreate($criteria, ['status' => 0])
                : Progress::where($criteria)->first();
        }

        $criteria = [
            'fk_usuarios_id' => $learner->id,
            'fk_atividades_id' => $activity->id,
        ];

        return $create
            ? UserProgress::firstOrCreate($criteria, ['status' => 0])
            : UserProgress::where($criteria)->first();
    }

    private function viewMap(string $materia): array
    {
        return strtolower($materia) === 'portugues'
            ? $this->statusToViewMapPt
            : $this->statusToViewMap;
    }

    public function iniciarProgresso(string $materia, int $numeroAtividade)
    {
        $this->findProgress($this->learner(), $materia, $numeroAtividade, true);

        return redirect()->route('atividade.view', compact('materia', 'numeroAtividade'));
    }

    public function iniciarProgressoPt(string $materia, int $numeroAtividade)
    {
        $this->findProgress($this->learner(), $materia, $numeroAtividade, true);

        return redirect()->route('atividade.viewPt', compact('materia', 'numeroAtividade'));
    }

    public function getProgress(string $materia, int $numeroAtividade)
    {
        return $this->progressResponse($materia, $numeroAtividade, false);
    }

    public function getProgressPt(string $materia, int $numeroAtividade)
    {
        return $this->progressResponse($materia, $numeroAtividade, true);
    }

    private function progressResponse(string $materia, int $numeroAtividade, bool $portuguese)
    {
        $progress = $this->findProgress($this->learner(), $materia, $numeroAtividade);
        $status = (int) ($progress?->status ?? 0);
        $map = $portuguese ? $this->statusToViewMapPt : $this->statusToViewMap;

        return response()->json([
            'status' => 'success',
            'message' => $progress ? 'Progresso encontrado.' : 'Nenhum progresso encontrado; iniciando do início.',
            'data' => [
                'status' => $status,
                'view' => $map[$status] ?? reset($map),
            ],
        ]);
    }

    public function updateProgress(Request $request)
    {
        return $this->saveProgress($request, false);
    }

    public function updateProgressPt(Request $request)
    {
        return $this->saveProgress($request, true);
    }

    private function saveProgress(Request $request, bool $portuguese)
    {
        $data = $request->validate([
            'materia' => ['required', 'string', 'max:15'],
            'numeroAtividade' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $learner = $this->learner();
        $progress = $this->findProgress(
            $learner,
            $data['materia'],
            (int) $data['numeroAtividade'],
            true
        );

        $progress->status = (int) $data['status'];
        $progress->save();

        $routeName = $portuguese ? 'atividade.viewPt' : 'atividade.view';
        $nextUrl = route($routeName, [
            'materia' => $data['materia'],
            'numeroAtividade' => $data['numeroAtividade'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Progresso atualizado com sucesso.',
            'next_url' => $nextUrl,
            'data' => ['status' => $progress->status],
        ]);
    }

    public function exibirAtividade(string $materia, int $numeroAtividade)
    {
        return $this->showActivity($materia, $numeroAtividade, false);
    }

    public function exibirAtividadePt(string $materia, int $numeroAtividade)
    {
        return $this->showActivity($materia, $numeroAtividade, true);
    }

    private function showActivity(string $materia, int $numeroAtividade, bool $portuguese)
    {
        $progress = $this->findProgress($this->learner(), $materia, $numeroAtividade);

        if (!$progress) {
            return redirect()->route($portuguese ? 'ModulesPt' : 'Modules')
                ->with('error', 'Inicie a atividade antes de acessá-la.');
        }

        $map = $portuguese ? $this->statusToViewMapPt : $this->statusToViewMap;
        $viewName = $map[(int) $progress->status] ?? reset($map);

        return view($viewName, compact('materia', 'numeroAtividade'));
    }
}
