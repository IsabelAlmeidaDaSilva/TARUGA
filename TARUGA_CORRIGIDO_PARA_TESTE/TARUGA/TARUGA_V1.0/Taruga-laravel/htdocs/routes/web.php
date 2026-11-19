<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/identification', [HomeController::class, 'nextPage'])->name('identification');
Route::get('/login', fn () => redirect()->route('identification'))->name('login');
Route::get('/register', fn () => redirect()->route('choose-register', 'user'))->name('register');

// Perfil selecionado para a tela de login/cadastro.
Route::get('/choose-login/{userType}', [AuthController::class, 'showLoginForm'])->name('choose-login');
Route::get('/choose-register/{userType}', [AuthController::class, 'showRegisterForm'])->name('choose-register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Cadastro independente e autenticação de alunos vinculados a professores.
Route::get('/student/register', [StudentAuthController::class, 'showRegisterForm'])->name('student.register.form');
Route::post('/student/register', [StudentAuthController::class, 'register'])->name('student.register');
Route::get('/student/login', [AuthController::class, 'showLoginForm'])->defaults('userType', 'student')->name('student.identification');
Route::post('/student/login', [StudentAuthController::class, 'login'])->name('student.login');
Route::post('/student/logout', [AuthController::class, 'logout'])->name('student.logout');

// Instituições podem se cadastrar publicamente; professores são criados dentro da área institucional.
Route::post('/institutions/register', [InstitutionController::class, 'registerInstitution'])->name('institution.register');
Route::get('/institutions/login', [InstitutionController::class, 'showLoginForm'])->name('institution.login');
Route::post('/institutions/login', [InstitutionController::class, 'login'])->name('institution.login.submit');

Route::post('/teacher/login', [TeacherController::class, 'login'])->name('teacher.login');

// Área de aprendizagem compartilhada por alunos institucionais e usuários independentes.
Route::middleware('auth:student,web')->group(function () {
    Route::get('/student/HomeStudent', fn () => view('homeUser'))->name('HomeStudent');
    Route::get('/student/SearchSubject', fn () => view('students.searchSubject'))->name('SearchSubject');
    Route::get('/student/Modules', fn () => view('students.modules'))->name('Modules');
    Route::get('/student/ModulesPt', fn () => view('students.modulesPt'))->name('ModulesPt');
    Route::get('/student/AboutUs', fn () => view('aboutUs'))->name('AboutUsStudent');

    Route::get('/student/activities/FirstActivity', fn () => view('students.activities.firstActivity.firstScreen'))
        ->name('activity.firstScreen');
    Route::get('/student/activities/FirstActivity/congratulations', fn () => view('students.activities.firstActivity.congratulations'))
        ->name('congratulations');

    Route::get('/students/progress/{materia}/{numeroAtividade}', [ProgressController::class, 'getProgress'])
        ->name('progress.get');
    Route::get('/studentsPt/progress/{materia}/{numeroAtividade}', [ProgressController::class, 'getProgressPt'])
        ->name('progress.getPt');
    Route::post('/modulos/iniciar/{materia}/{numeroAtividade}', [ProgressController::class, 'iniciarProgresso'])
        ->name('iniciar.progresso');
    Route::post('/modulosPt/iniciar/{materia}/{numeroAtividade}', [ProgressController::class, 'iniciarProgressoPt'])
        ->name('iniciar.progressoPt');
    Route::get('/atividade/{materia}/{numeroAtividade}', [ProgressController::class, 'exibirAtividade'])
        ->name('atividade.view');
    Route::get('/atividadePt/{materia}/{numeroAtividade}', [ProgressController::class, 'exibirAtividadePt'])
        ->name('atividade.viewPt');
    Route::post('/progress/update', [ProgressController::class, 'updateProgress'])->name('progress.update');
    Route::post('/progressPt/update', [ProgressController::class, 'updateProgressPt'])->name('progress.updatePt');
});

// Funcionalidades exclusivas do aluno cadastrado pela instituição.
Route::middleware('auth:student')->group(function () {
    Route::get('/student/Profile', fn () => view('students.profile_student'))->name('Profile');
    Route::post('/profile/photo', [StudentAuthController::class, 'updatePhoto'])->name('select.photo');
});

// Professor só pode consultar e modificar alunos sob sua própria responsabilidade.
Route::middleware('auth:teacher')->group(function () {
    Route::get('/teacher/HomeTeacher', fn () => view('homeUser'))->name('HomeTeacher');
    Route::get('/teacher/AboutUs', fn () => view('aboutUs'))->name('AboutUsTeacher');
    Route::get('/teacher/List', [TeacherController::class, 'teacherList'])->name('teacher.list');
    Route::get('/students', [StudentAuthController::class, 'search'])->name('students.search');
    Route::post('/students', [StudentAuthController::class, 'registerStudent'])->name('student.register.t');
    Route::put('/students/{id}', [StudentAuthController::class, 'update'])->name('students.update');
    Route::delete('/students/{id}', [StudentAuthController::class, 'delete'])->name('students.delete');
    Route::post('/teacher/update-password', [TeacherController::class, 'updatePassword'])->name('teacher.update-password');
});

// Instituição só gerencia professores pertencentes a ela.
Route::middleware('auth:institution')->group(function () {
    Route::get('/institution/HomeInstitution', fn () => view('homeUser'))->name('HomeInstitution');
    Route::get('/institution/List', [InstitutionController::class, 'institutionList'])->name('institution.list');
    Route::get('/teachers', [TeacherController::class, 'search'])->name('teachers.search');
    Route::post('/teachers', [TeacherController::class, 'registerTeacher'])->name('teachers.store');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
    Route::get('/institution/AboutUs', fn () => view('aboutUs'))->name('AboutUsInstitution');
});

Route::get('/contact', fn () => view('contact'))->name('contact');
Route::post('/send-email', [SupportController::class, 'sendEmail'])->name('send.email');
