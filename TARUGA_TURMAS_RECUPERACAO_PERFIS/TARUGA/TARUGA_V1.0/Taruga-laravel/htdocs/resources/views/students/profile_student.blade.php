@extends('layouts.headerFooter')
@section('title','Meu perfil - Taruga')
@push('styles')<link rel="stylesheet" href="{{asset('css/profile_student.css')}}?v=4">@endpush
@section('content')
<div class="container taruga-panel py-4"><a class="btn btn-outline-success mb-3" href="{{route('HomeStudent')}}">← Início</a><h1>Meu perfil</h1>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{implode(' · ',$errors->all())}}</div>@endif
<div class="caixa-perfil taruga-profile-grid"><aside class="sidebar taruga-profile-sidebar">
 <div id="icon-perfil"><img src="{{$learner->foto?asset(str_starts_with($learner->foto, 'uploads/')?$learner->foto:'storage/'.$learner->foto):asset('storage/imgs/Taruga_Icon.png')}}" alt="Minha foto de perfil" style="max-width:125px;max-height:125px;object-fit:cover;border-radius:50%"></div>
 <div class="info"><strong>{{$student?$student->nome:$learner->name}}</strong></div><div class="info">{{$learner->email}}</div>
 @if($student)<div class="info">Turma: {{$student->class?->nome ?? 'Não atribuída'}}</div><div class="info">Professor(a): {{$student->teacher?->nome ?? 'Não atribuído'}}</div><div class="info">{{$student->situacao}}</div>
 @else <div class="info">Usuário independente</div> @endif
 <form method="POST" action="{{route('learning.profile.photo')}}" enctype="multipart/form-data" class="mt-3">@csrf
 <label class="form-label" for="profile-photo">Escolher foto de perfil</label><input id="profile-photo" class="form-control form-control-sm" type="file" name="foto" accept="image/jpeg,image/png,image/webp" required><small class="d-block text-muted">JPG, PNG ou WebP; até 2 MB.</small><button class="btn btn-success btn-sm mt-2">Salvar foto</button></form>
</aside><div class="main-conteudo taruga-profile-main"><h2>Meu progresso por matéria</h2>
<p>Cada matéria mostra a porcentagem de atividades diferentes já concluídas; novas atividades entram no total quando são cadastradas.</p>
@foreach($subjectProgress as $subject=>$progress)
<section class="taruga-surface mb-3"><div class="d-flex justify-content-between gap-2"><h3 class="h5">{{$progress['label']}}</h3><strong>{{$progress['percent']}}%</strong></div>
<div class="progress" style="height:18px"><div class="progress-bar bg-success" role="progressbar" style="width:{{$progress['percent']}}%" aria-valuenow="{{$progress['percent']}}" aria-valuemin="0" aria-valuemax="100" aria-label="Progresso em {{$progress['label']}}"></div></div>
<small>{{$progress['completed']}} de {{$progress['total']}} atividades diferentes concluídas</small></section>
@endforeach
<div class="taruga-page-title mt-3"><h2>Minha coleção de conquistas</h2><a href="{{route('learning.achievements')}}">Ver todas</a></div>
<div class="row g-2">@foreach($stickers as $sticker)<div class="col-6 col-lg-4"><div class="taruga-surface taruga-sticker {{$sticker['unlocked']?'':'taruga-sticker-locked'}}"><span class="taruga-sticker-icon">{{$sticker['unlocked']?$sticker['icon']:'🔒'}}</span><strong class="d-block">{{$sticker['name']}}</strong><small>{{$sticker['unlocked']?'Desbloqueada':$sticker['description']}}</small></div></div>@endforeach</div>
<a class="btn btn-success mt-3" href="{{route('learning.results')}}">Ver notas e histórico</a></div></div></div>
@endsection
