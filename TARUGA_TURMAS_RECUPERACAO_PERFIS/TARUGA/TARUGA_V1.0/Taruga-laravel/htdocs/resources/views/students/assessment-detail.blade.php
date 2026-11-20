@extends('layouts.headerFooter')
@section('title','Minha avaliação - Taruga')
@section('content')
<div class="container taruga-panel py-4"><a href="{{route('student.assessments')}}" class="btn btn-outline-success mb-3">← Minhas avaliações</a>
<h1>{{$assessment->titulo}}</h1><p>{{$assessment->descricao}}</p>
<div class="taruga-surface"><p><strong>Tentativa {{$assessment->pivot->attempt_number}}</strong> · {{$assessment->prazo?'Prazo: '.$assessment->prazo->format('d/m/Y H:i'):'Sem prazo definido'}}</p>
@if($finished)<div class="alert alert-success"><h2 class="h4">Avaliação finalizada!</h2><p>Suas notas foram registradas. Apenas o professor pode liberar uma recuperação.</p><a class="btn btn-success" href="{{route('learning.results')}}#avaliacoes">Ver notas e histórico</a></div>
@elseif($locked)<div class="alert alert-warning"><h2 class="h4">Avaliação indisponível</h2><p>O prazo terminou ou o professor finalizou a prova. Consulte suas notas ou solicite uma reabertura.</p><a href="{{route('learning.results')}}#avaliacoes">Consultar resultados</a></div>
@endif
<h2>Atividades desta tentativa</h2><div class="list-group">@forelse($activities as $activity)
<div class="list-group-item d-flex justify-content-between align-items-center gap-3 flex-wrap"><div><strong>{{$activity->nome}}</strong><small class="d-block text-muted">{{ucfirst($activity->nomeMateria)}} · Módulo {{$activity->module?->numeroModulo ?? 1}}</small></div>
@if(in_array($activity->id,$doneIds))<span class="badge bg-success">Concluída</span>
@elseif(!$locked && !$finished)<a class="btn btn-success" href="{{route('student.assessments.activity',[$assessment->id,$activity->id])}}">Fazer atividade →</a>
@else<span class="badge bg-secondary">Indisponível</span>@endif
</div>@empty <p>Esta avaliação não possui atividades.</p>@endforelse</div>
</div><a class="btn btn-outline-success mt-3" href="{{route('learning.results')}}#avaliacoes">Ver resultados e histórico</a></div>
@endsection
