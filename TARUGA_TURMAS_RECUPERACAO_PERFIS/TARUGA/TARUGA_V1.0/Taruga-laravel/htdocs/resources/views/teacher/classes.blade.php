@extends('layouts.headerFooter')
@section('title','Minhas turmas - Taruga')
@section('content')
<div class="container taruga-panel py-4"><div class="taruga-page-title"><h1>Minhas turmas</h1><a class="btn btn-outline-success" href="{{route('teacher.assessments')}}">Avaliações</a></div>
<p>Organize seus alunos por turma. Clique em uma turma para abrir seus alunos e acompanhar a média do grupo.</p>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' · ', $errors->all())}}</div>@endif
<div class="taruga-surface mb-4"><h2>Criar turma</h2><form method="POST" action="{{route('teacher.classes.store')}}" class="row g-3">@csrf<div class="col-md-5"><label class="form-label">Nome</label><input name="nome" maxlength="25" required class="form-control" placeholder="Ex.: Turma A" value="{{old('nome')}}"></div><div class="col-md-5"><label class="form-label">Série / período</label><input name="serie" maxlength="25" required class="form-control" placeholder="Ex.: 3º ano" value="{{old('serie')}}"></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-success w-100">Criar</button></div></form></div>
<div class="row g-3">@forelse($classes as $class)<div class="col-md-6"><a class="taruga-surface taruga-class-card" href="{{route('teacher.classes.show',$class->id)}}"><h3>{{ $class->nome }}</h3><p>{{ $class->serie }}</p><span><i class="bi bi-people-fill"></i> {{ $class->students->count() }} aluno(s)</span></a></div>@empty<div class="col-12"><div class="taruga-surface">Você ainda não criou turmas. Comece pelo formulário acima.</div></div>@endforelse</div>
<a href="{{route('teacher.list')}}" class="btn btn-outline-success mt-4">Ver todos os alunos</a> <a href="{{route('teacher.students.create')}}" class="btn btn-success mt-4">Cadastrar aluno</a></div>
@endsection
