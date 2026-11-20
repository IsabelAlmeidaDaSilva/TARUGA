<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\Institution;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TarugaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_independent_user_can_register_login_and_use_learning_area(): void
    {
        $this->post(route('student.register'), [
            'nome' => 'Usuário de teste',
            'email' => 'usuario@example.test',
            'senha' => 'SenhaSegura123',
            'senha_confirmation' => 'SenhaSegura123',
        ])->assertRedirect(route('identification'));

        $user = User::where('email', 'usuario@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('SenhaSegura123', $user->password));

        $this->withSession(['user_type' => 'user'])
            ->post(route('auth.login'), [
                'email' => 'usuario@example.test',
                'senha' => 'SenhaSegura123',
            ])
            ->assertRedirect(route('HomeStudent'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->get(route('Modules'))->assertOk();
        $this->get(route('HomeTeacher'))->assertRedirect(route('identification'));
    }

    public function test_student_login_is_limited_to_student_area(): void
    {
        [$institution, $teacher, $class] = $this->createInstitutionTeacherAndClass();
        $student = $teacher->students()->create([
            'nome' => 'Aluno de teste',
            'ra' => '0000000001',
            'email' => 'aluno@example.test',
            'senha' => Hash::make('SenhaSegura123'),
            'situacao' => 'Ouvinte',
            'foto' => '',
            'fk_turmas_id' => $class->id,
        ]);

        $this->withSession(['user_type' => 'student'])
            ->post(route('auth.login'), [
                'email' => 'aluno@example.test',
                'senha' => 'SenhaSegura123',
            ])
            ->assertRedirect(route('HomeStudent'));

        $this->assertAuthenticatedAs($student, 'student');
        $this->get(route('HomeStudent'))->assertOk();
        $this->get(route('HomeInstitution'))->assertRedirect(route('identification'));
    }

    public function test_teacher_can_only_manage_students_under_their_own_account(): void
    {
        [, $teacher] = $this->createInstitutionTeacherAndClass();
        $otherInstitution = Institution::create([
            'nome' => 'Outra instituição',
            'cnpj' => '11.111.111/0001-11',
            'endereco' => 'Rua Teste, 10',
            'telefone' => '(47) 3333-4444',
            'email' => 'outra@example.test',
            'senha' => Hash::make('SenhaSegura123'),
        ]);
        $otherTeacher = $otherInstitution->teachers()->create([
            'nome' => 'Outro professor',
            'rp' => '0000000002',
            'email' => 'outro.prof@example.test',
            'senha' => Hash::make('SenhaSegura123'),
            'precisa_atualizar_senha' => false,
        ]);
        $otherClass = $otherTeacher->classes()->create([
            'nome' => 'Turma B',
            'serie' => '4º ano',
        ]);
        $otherStudent = $otherTeacher->students()->create([
            'nome' => 'Aluno de outra escola',
            'ra' => '0000000002',
            'email' => 'outro.aluno@example.test',
            'senha' => Hash::make('SenhaSegura123'),
            'situacao' => 'Ouvinte',
            'foto' => '',
            'fk_turmas_id' => $otherClass->id,
        ]);

        $this->actingAs($teacher, 'teacher');
        $this->put(route('students.update', $otherStudent->id), [
            'nome' => 'Tentativa de alteração',
        ])->assertNotFound();

        $this->get(route('institution.list'))->assertRedirect(route('identification'));
    }

    public function test_institution_can_register_teacher_and_teacher_can_login(): void
    {
        $institution = Institution::create([
            'nome' => 'Instituição de teste',
            'cnpj' => '22.222.222/0001-22',
            'endereco' => 'Rua Teste, 20',
            'telefone' => '(47) 99999-8888',
            'email' => 'instituicao@example.test',
            'senha' => Hash::make('SenhaSegura123'),
        ]);

        $this->withSession(['user_type' => 'institution'])
            ->post(route('auth.login'), [
                'email' => 'instituicao@example.test',
                'senha' => 'SenhaSegura123',
            ])
            ->assertRedirect(route('HomeInstitution'));

        $this->post(route('teachers.store'), [
            'nome' => 'Professor criado',
            'rp' => '0000000099',
            'email' => 'professor.criado@example.test',
            'senha' => 'SenhaSegura123',
        ])->assertSessionHas('success');

        $teacher = Teacher::where('email', 'professor.criado@example.test')->firstOrFail();
        $this->assertSame($institution->id, $teacher->fk_instituicao_id);
        $this->assertTrue(Hash::check('SenhaSegura123', $teacher->senha));

        auth('institution')->logout();
        $this->withSession(['user_type' => 'teacher'])
            ->post(route('auth.login'), [
                'email' => 'professor.criado@example.test',
                'senha' => 'SenhaSegura123',
            ])
            ->assertRedirect(route('HomeTeacher'));

        $this->assertAuthenticatedAs($teacher, 'teacher');
    }

    private function createInstitutionTeacherAndClass(): array
    {
        $institution = Institution::create([
            'nome' => 'Instituição de teste',
            'cnpj' => '33.333.333/0001-33',
            'endereco' => 'Rua Teste, 30',
            'telefone' => '(47) 3333-3333',
            'email' => 'instituicao.base@example.test',
            'senha' => Hash::make('SenhaSegura123'),
        ]);

        $teacher = $institution->teachers()->create([
            'nome' => 'Professor de teste',
            'rp' => '0000000001',
            'email' => 'professor.base@example.test',
            'senha' => Hash::make('SenhaSegura123'),
            'precisa_atualizar_senha' => false,
        ]);

        $class = $teacher->classes()->create([
            'nome' => 'Turma A',
            'serie' => '4º ano',
        ]);

        return [$institution, $teacher, $class];
    }
}
