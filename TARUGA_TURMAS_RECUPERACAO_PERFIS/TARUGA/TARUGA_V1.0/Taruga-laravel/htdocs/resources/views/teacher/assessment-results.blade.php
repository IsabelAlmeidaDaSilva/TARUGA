@extends('layouts.headerFooter')
@section('title','Resultados e recuperação - Taruga')
@section('content')
<div class="container taruga-panel py-4">
 <a class="btn btn-outline-success mb-3" href="{{route('teacher.assessments')}}">← Avaliações</a>
 <h1>{{$assessment->titulo}}</h1><p>Resultados da tentativa atual. As tentativas anteriores permanecem no histórico do aluno.</p>
 @if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{implode(' · ',$errors->all())}}</div>@endif
 <div class="row g-3 mb-3"><div class="col-md-6"><div class="taruga-surface"><h2>{{$classAverage===null?'—':number_format($classAverage,2,',','.').'/10'}}</h2><p>Média dos alunos que concluíram a tentativa atual desta avaliação</p></div></div><div class="col-md-6"><div class="taruga-surface"><h2>{{$completedStudents}} / {{count($report)}}</h2><p>Alunos com tentativa atual concluída</p></div></div></div>
 <div class="taruga-surface"><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Aluno e turma</th><th>Tentativa</th><th>Conclusão</th><th>Nota</th><th>Recuperação individual</th></tr></thead><tbody>
 @forelse($report as $row)<tr>
 <td><a href="{{route('teacher.students.performance',$row['student']->id)}}">{{$row['student']->nome}}</a><small class="d-block text-muted">{{$row['student']->class?->nome ?? 'Sem turma'}}</small></td>
 <td>{{$row['attempt']}}</td><td>{{$row['completed']}}/{{$row['totalActivities']}} atividades</td>
 <td>{{$row['score']===null?'Sem nota':number_format($row['score'],2,',','.').'/10'}} @if($row['completed']<$row['totalActivities'])<small class="d-block">Em andamento</small>@endif</td>
 <td><form method="POST" action="{{route('teacher.assessments.reopen',[$assessment->id,$row['student']->id])}}" class="d-flex flex-wrap align-items-center gap-2">@csrf
 <label class="visually-hidden" for="recovery-{{$row['student']->id}}">Atividade alternativa</label>
 <select class="form-select form-select-sm" id="recovery-{{$row['student']->id}}" name="recovery_activity_id" style="min-width:175px;max-width:260px"><option value="">Repetir atividades da prova</option>@foreach($activities as $activity)<option value="{{$activity->id}}">{{ucfirst($activity->nomeMateria)}} · {{$activity->nome}}</option>@endforeach</select>
 <button class="btn btn-sm btn-outline-primary" @disabled(!$assessment->ativa || !$row['completed']) title="O aluno deve ter concluído ao menos uma atividade; a avaliação precisa estar aberta">Liberar recuperação</button></form></td>
 </tr>@empty <tr><td colspan="5">Nenhum aluno atribuído.</td></tr> @endforelse
 </tbody></table></div><small class="text-muted">A recuperação abre uma nova tentativa apenas para o aluno escolhido. Se selecionar uma atividade alternativa, ela substitui as atividades originais nesta tentativa, sem alterar a prova dos colegas.</small></div>
</div>
@endsection
