<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityResult;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearnerProfileController extends Controller
{
    private function learner()
    {
        $learner = Auth::guard('student')->user() ?: Auth::guard('web')->user();
        abort_unless($learner, 403);
        return $learner;
    }

    public function show()
    {
        $learner = $this->learner();
        $student = $learner instanceof Student ? $learner->load(['teacher', 'class']) : null;
        $query = ActivityResult::where($student ? 'student_id' : 'user_id', $learner->id);
        $finishedIds = (clone $query)->pluck('activity_id')->unique();
        $subjectProgress = collect(['libras' => 'Libras', 'portugues' => 'Português'])->map(function ($label, $subject) use ($finishedIds) {
            $activities = Activity::where('nomeMateria', $subject)->get();
            $completed = $activities->filter(fn ($activity) => $finishedIds->contains($activity->id))->count();
            return ['label' => $label, 'completed' => $completed, 'total' => $activities->count(),
                'percent' => $activities->count() ? round($completed * 100 / $activities->count()) : 0];
        });
        $stickers = app(AchievementController::class)->stickersFor($learner);
        return view('students.profile_student', compact('learner', 'student', 'subjectProgress', 'stickers'));
    }

    public function updatePhoto(Request $request)
    {
        $learner = $this->learner();
        $request->validate(['foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        // The original project has a real public/storage folder, not a Laravel storage symlink.
        // Save uploads to a separate public directory so the photo can be displayed without replacing it.
        $directory = public_path('uploads/profiles');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return back()->withErrors(['foto' => 'Não foi possível preparar o diretório de fotos.']);
        }
        $fileName = Str::uuid().'.'.$request->file('foto')->extension();
        $request->file('foto')->move($directory, $fileName);
        $oldPhoto = $learner->foto;
        $learner->foto = 'uploads/profiles/'.$fileName;
        $learner->save();
        if ($oldPhoto && str_starts_with($oldPhoto, 'uploads/profiles/')) {
            $oldPath = public_path($oldPhoto);
            if (is_file($oldPath)) unlink($oldPath);
        } elseif ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }
        return back()->with('success', 'Foto atualizada.');
    }
}
