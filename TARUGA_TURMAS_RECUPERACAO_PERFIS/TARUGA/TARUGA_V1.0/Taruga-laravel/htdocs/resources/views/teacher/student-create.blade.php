@extends('layouts.headerFooter')
@section('title','Cadastrar aluno - Taruga')
@section('content')
<div class="container taruga-panel py-4">
 <div class="taruga-page-title"><h1>Cadastrar aluno</h1><a class="btn btn-outline-success" href="{{route('teacher.list')}}">Ver meus alunos</a></div>
 <p>Cadastre um aluno e escolha a turma. O aluno utilizará o e-mail e a senha para entrar no Taruga.</p>
 @if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
 <div class="taruga-surface">
  @if($classes->isEmpty())<p class="alert alert-info">Você ainda não criou uma turma. <a href="{{route('teacher.classes')}}">Crie sua primeira turma</a> antes de cadastrar alunos.</p>@endif
  <form method="POST" action="{{route('student.register.t')}}" class="row g-3">@csrf
    <div class="col-md-6"><label class="form-label" for="nome">Nome completo</label><input id="nome" class="form-control" name="nome" value="{{old('nome')}}" required maxlength="55"></div>
    <div class="col-md-6"><label class="form-label" for="ra">RA (registro do aluno)</label><input id="ra" class="form-control" name="ra" value="{{old('ra')}}" required maxlength="25"></div>
    <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input id="email" type="email" class="form-control" name="email" value="{{old('email')}}" required maxlength="55"></div>
    <div class="col-md-6"><label class="form-label" for="senha">Senha inicial</label><input id="senha" type="password" class="form-control" name="senha" required minlength="8" autocomplete="new-password"></div>
    <div class="col-md-6"><label class="form-label" for="situacao">Situação</label><select id="situacao" name="situacao" class="form-select" required><option value="">Selecione</option><option value="Ouvinte" @selected(old('situacao')==='Ouvinte')>Ouvinte</option><option value="Não-ouvinte" @selected(old('situacao')==='Não-ouvinte')>Não-ouvinte</option></select></div>
    <div class="col-md-6"><label class="form-label" for="class_id">Turma</label><select id="class_id" name="class_id" class="form-select" required><option value="">Selecione uma turma</option>@foreach($classes as $class)<option value="{{$class->id}}" @selected(old('class_id',request('class_id'))==$class->id)>{{$class->nome}} — {{$class->serie}}</option>@endforeach</select></div>
    <div class="col-12"><button class="btn btn-success" @disabled($classes->isEmpty())>Cadastrar aluno</button><a class="btn btn-outline-success ms-2" href="{{route('teacher.classes')}}">Gerenciar turmas</a></div>
  </form>
 </div>
</div>
@endsection
