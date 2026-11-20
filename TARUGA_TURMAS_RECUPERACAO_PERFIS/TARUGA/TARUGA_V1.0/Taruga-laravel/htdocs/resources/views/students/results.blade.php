@extends('layouts.headerFooter')
@section('title','Resultados e histórico - Taruga')
@section('content')
<div class="container taruga-panel py-4" id="resultados"><div class="taruga-page-title"><h1>Resultados e histórico</h1><a class="btn btn-outline-success" href="{{route('SearchSubject')}}">Ir para Prática</a></div>
<p>Seus resultados estão divididos entre atividades livres e avaliações, com separação por matéria.</p>
@php $all=$results->concat($assessmentResults); $average=$all->isNotEmpty()?round($all->avg('score'),2):null; @endphp
<div id="avaliacoes"></div><section class="taruga-surface mb-4"><h2>Meu resumo</h2><div class="row g-3"><div class="col-sm-4"><strong class="taruga-metric">{{$all->count()}}</strong><p>Atividades registradas</p></div><div class="col-sm-4"><strong class="taruga-metric">{{$average===null?'—':number_format($average,2,',','.').'/10'}}</strong><p>Média geral</p></div><div class="col-sm-4"><strong class="taruga-metric">{{$assessmentResults->count()}}</strong><p>Registros de avaliações</p></div></div>
<details class="taruga-summary"><summary class="taruga-summary-toggle">Como são calculadas as notas e o progresso?</summary><p class="mt-3">Cada nota corresponde aos acertos divididos pelo total de questões, multiplicado por dez. A média geral usa todas as tentativas registradas; as recuperações não apagam notas anteriores. O progresso do perfil é calculado pelas atividades diferentes concluídas em cada matéria.</p></details></section>
@foreach(['libras'=>'Libras','portugues'=>'Português'] as $subject=>$label)
@php $free=$results->filter(fn($r)=>strtolower($r->activity?->nomeMateria??'')===$subject); $exam=$assessmentResults->filter(fn($r)=>strtolower($r->activity?->nomeMateria??'')===$subject); @endphp
<section class="taruga-surface mb-4" id="{{$subject}}"><h2>{{$label}}</h2>
<div class="row g-2 mb-2"><div class="col-sm-6">Práticas registradas: <strong>{{$free->count()}}</strong></div><div class="col-sm-6">Registros de avaliações: <strong>{{$exam->count()}}</strong></div></div>
<h3 class="h5" id="pratica-{{$subject}}">Histórico de atividades livres</h3><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Atividade</th><th>Acertos</th><th>Erros</th><th>Nota</th><th>Data e hora</th></tr></thead><tbody>
@forelse($free as $r)<tr><td>{{$r->activity?->nome??'Atividade'}}</td><td>{{$r->correct}}/{{$r->total}}</td><td>{{$r->errors}}</td><td>{{number_format($r->score,2,',','.')}}/10</td><td>{{$r->completed_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i')}}</td></tr>@empty<tr><td colspan="5">Ainda não há prática registrada em {{$label}}.</td></tr>@endforelse</tbody></table></div>
@if(auth('student')->check())<h3 class="h5 mt-4">Resultados de avaliações</h3><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Avaliação</th><th>Atividade</th><th>Tentativa</th><th>Acertos</th><th>Nota</th><th>Data e hora</th></tr></thead><tbody>
@forelse($exam as $r)<tr><td>{{$r->assessment?->titulo??'Avaliação removida'}}</td><td>{{$r->activity?->nome??'Atividade'}}</td><td>{{$r->attempt_number}}</td><td>{{$r->correct}}/{{$r->total}}</td><td>{{number_format($r->score,2,',','.')}}/10</td><td>{{$r->completed_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i')}}</td></tr>@empty<tr><td colspan="6">Nenhuma nota de avaliação em {{$label}}.</td></tr>@endforelse</tbody></table></div>@endif
</section>
@endforeach
</div>
@endsection
