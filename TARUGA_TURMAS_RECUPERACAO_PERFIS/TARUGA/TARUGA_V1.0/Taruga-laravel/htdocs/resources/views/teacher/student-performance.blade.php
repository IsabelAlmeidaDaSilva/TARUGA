@extends('layouts.headerFooter')
@section('title','Desempenho do aluno - Taruga')
@section('content')
<div class="container taruga-panel py-4">
 <a class="btn btn-outline-success mb-3" href="{{route('teacher.performance')}}">← Voltar aos alunos</a>
 <div class="taruga-page-title"><h1>{{$student->nome}}</h1><span class="badge bg-success">{{ $student->class?->nome ?? 'Sem turma' }}</span></div>
 <p>RA: {{$student->ra}} · {{$student->email}} · {{$student->situacao}}</p>
 <div class="row g-3 mb-4"><div class="col-sm-4"><div class="taruga-surface"><h2>{{ $results->count() }}</h2><p>Resultados registrados</p></div></div><div class="col-sm-4"><div class="taruga-surface"><h2>{{$mean===null?'—':number_format($mean,2,',','.').'/10'}}</h2><p>Média geral</p></div></div><div class="col-sm-4"><div class="taruga-surface"><h2>{{ $student->progresses->where('status','>=',100)->count() }}</h2><p>Atividades livres finalizadas (progresso)</p></div></div></div>
 <div class="taruga-surface mb-3"><h2>Avaliações</h2><div class="table-responsive"><table class="table"><thead><tr><th>Avaliação</th><th>Atividade</th><th>Acertos</th><th>Nota</th><th>Data</th></tr></thead><tbody>@forelse($assessmentResults as $r)<tr><td>{{$r->assessment?->titulo ?? 'Avaliação'}}</td><td>{{$r->activity?->nome ?? 'Atividade'}}</td><td>{{$r->correct}}/{{$r->total}}</td><td>{{number_format($r->score,2,',','.')}}</td><td>{{$r->completed_at?->format('d/m/Y H:i')}}</td></tr>@empty<tr><td colspan="5">Ainda sem notas de avaliação.</td></tr>@endforelse</tbody></table></div></div>
 <div class="taruga-surface"><h2>Atividades livres e histórico</h2><div class="table-responsive"><table class="table"><thead><tr><th>Atividade</th><th>Acertos</th><th>Erros</th><th>Nota</th><th>Data</th></tr></thead><tbody>@forelse($freeResults as $r)<tr><td>{{$r->activity?->nome ?? 'Atividade'}}</td><td>{{$r->correct}}/{{$r->total}}</td><td>{{$r->errors}}</td><td>{{number_format($r->score,2,',','.')}}</td><td>{{$r->completed_at?->format('d/m/Y H:i')}}</td></tr>@empty<tr><td colspan="5">Ainda sem atividades registradas.</td></tr>@endforelse</tbody></table></div></div>
</div>
@endsection
