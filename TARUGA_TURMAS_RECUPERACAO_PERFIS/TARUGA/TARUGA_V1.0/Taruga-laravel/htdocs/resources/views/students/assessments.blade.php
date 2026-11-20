@extends('layouts.headerFooter')
@section('title','Minhas avaliações - Taruga')
@section('content')
<div class="container taruga-panel py-4"><div class="taruga-page-title"><h1>Minhas avaliações</h1><a class="btn btn-outline-success" href="{{route('learning.results')}}#avaliacoes">Notas e histórico</a></div>
<p>Cada atividade da prova pode ser realizada uma vez por tentativa. Somente o professor pode liberar uma recuperação.</p>
<div class="row g-3">@forelse($assessments as $assessment)
@php
 $done=($latest->get($assessment->id.'-'.$assessment->pivot->attempt_number,collect()))->pluck('activity_id')->unique();
 $total=($assessment->pivot->attempt_number>1 && $assessment->pivot->recovery_activity_id)?1:$assessment->activities->count();
 $completed=$total>0 && $done->count()>=$total;
 $expired=$assessment->prazo && now()->greaterThan($assessment->prazo) && $assessment->pivot->attempt_number<=1;
 $locked=!$assessment->ativa || $expired;
@endphp
<div class="col-md-6"><div class="taruga-surface h-100"><h2>{{$assessment->titulo}}</h2><p>{{$assessment->descricao}}</p><p>Tentativa {{$assessment->pivot->attempt_number}} · Atividades concluídas: <strong>{{$done->count()}}/{{$total}}</strong></p>
@if($assessment->prazo)<p>Prazo: {{$assessment->prazo->format('d/m/Y H:i')}}</p>@endif
@if($completed)<span class="badge bg-success d-inline-block mb-2">Avaliação finalizada</span>
@elseif($locked)<span class="badge bg-secondary d-inline-block mb-2">{{$assessment->ativa?'Prazo encerrado':'Encerrada pelo professor'}}</span>
@endif
<a class="btn {{$completed||$locked?'btn-outline-success':'btn-success'}} d-block" href="{{route('student.assessments.show',$assessment->id)}}">{{$completed?'Ver avaliação finalizada':($locked?'Consultar avaliação':'Abrir avaliação')}}</a></div></div>
@empty <div class="taruga-surface"><p>Você ainda não tem avaliações atribuídas.</p><a class="btn btn-success" href="{{route('SearchSubject')}}">Ir para Prática</a></div> @endforelse</div></div>
@endsection
