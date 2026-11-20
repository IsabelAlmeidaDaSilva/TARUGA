@extends('layouts.headerFooter')
@section('title','Desempenho das turmas - Taruga')
@section('content')
<div class="container taruga-panel py-4">
 <div class="taruga-page-title"><h1>Desempenho dos alunos</h1><a class="btn btn-outline-success" href="{{route('teacher.students.create')}}">Cadastrar aluno</a></div>
 <form method="GET" action="{{route('teacher.performance')}}" class="taruga-surface mb-4"><label for="class-filter" class="form-label">Filtrar por turma</label><div class="row g-2"><div class="col-sm-8"><select name="class_id" id="class-filter" class="form-select"><option value="">Todas as turmas</option>@foreach($classes as $class)<option value="{{$class->id}}" @selected($selectedClass?->id===$class->id)>{{$class->nome}} — {{$class->serie}}</option>@endforeach</select></div><div class="col-sm-4"><button class="btn btn-success w-100">Ver desempenho</button></div></div></form>
 <div class="taruga-surface mb-4"><h2>{{ $selectedClass ? 'Média da turma: '.$selectedClass->nome : 'Média geral de todas as turmas' }}</h2><strong class="taruga-metric">{{$mean===null?'—':number_format($mean,2,',','.').'/10'}}</strong><p class="text-muted mb-0">Calculada a partir das notas registradas pelos alunos {{ $selectedClass ? 'desta turma' : 'de todas as turmas' }}.</p></div>
 <p>Clique em um aluno para abrir seu histórico e desempenho completo.</p>
 <div class="row g-3">@forelse($students as $student)
 @php $items=$student->results; $average=$items->count()?round($items->avg('score'),2):null; @endphp
 <div class="col-md-6"><a class="taruga-surface taruga-class-card" href="{{route('teacher.students.performance',$student->id)}}"><h3>{{$student->nome}}</h3><p>{{$student->class?->nome ?? 'Sem turma'}}</p><p>Atividades: <strong>{{$items->count()}}</strong> · Média: <strong>{{$average===null?'Sem notas':number_format($average,2,',','.').'/10'}}</strong></p><span class="taruga-cta-link">Ver desempenho completo →</span></a></div>
 @empty <div class="col-12"><div class="taruga-surface">Nenhum aluno encontrado neste filtro. <a href="{{route('teacher.list')}}">Ver lista de alunos</a>.</div></div> @endforelse</div>
</div>
@endsection
