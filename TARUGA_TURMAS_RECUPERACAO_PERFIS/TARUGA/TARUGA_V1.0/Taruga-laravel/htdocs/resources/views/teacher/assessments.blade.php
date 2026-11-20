@extends('layouts.headerFooter')
@section('title','Avaliações - Taruga')
@section('content')
<div class="container taruga-panel py-4">
 <div class="taruga-page-title"><h1>Avaliações</h1><a class="btn btn-outline-success" href="{{route('teacher.classes')}}">Minhas turmas</a></div>
 <p>Monte uma prova com as atividades do Taruga e atribua para uma turma inteira ou para alunos específicos.</p>
 @if(session('success'))<div class="alert alert-success" role="status">{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Verifique os campos:</strong><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
 <div class="taruga-surface mb-4"><h2>Criar avaliação</h2>
  <form method="POST" action="{{route('teacher.assessments.store')}}" id="assessment-form">@csrf
   <div class="row g-3">
    <div class="col-md-8"><label class="form-label" for="exam-title">Título da avaliação</label><input id="exam-title" name="titulo" class="form-control" maxlength="120" required value="{{old('titulo')}}"></div>
    <div class="col-md-4"><label class="form-label" for="exam-date">Data do prazo (opcional)</label><input type="date" id="exam-date" name="prazo_data" value="{{old('prazo_data')}}" class="form-control"></div>
    <div class="col-md-8"><label class="form-label" for="exam-description">Descrição (opcional)</label><textarea id="exam-description" name="descricao" rows="2" class="form-control">{{old('descricao')}}</textarea></div>
    <div class="col-md-4"><label class="form-label" for="exam-time">Hora do prazo (opcional)</label><input type="time" id="exam-time" step="60" name="prazo_hora" value="{{old('prazo_hora')}}" class="form-control"><small class="text-muted">Formato 24 horas (horário local).</small></div>
   </div>
   <h3 class="h5 mt-4">Escolha as atividades por módulo</h3>
   <label class="form-label" for="activity-search">Pesquisar atividade ou módulo</label><input id="activity-search" type="search" class="form-control mb-2" placeholder="Ex.: Libras, alfabeto, português..." aria-label="Pesquisar atividades">
   <div id="taruga-activity-groups" class="taruga-scroll-select mb-3">
    @forelse($modules as $module)
     @if($module->activities->isNotEmpty())
     <details class="taruga-module-choice mb-2" data-search="{{mb_strtolower($module->tema.' módulo '.$module->numeroModulo.' '.$module->activities->pluck('nome')->join(' '))}}">
      <summary><strong>{{ucfirst($module->tema)}} · Módulo {{$module->numeroModulo}}</strong> <span class="text-muted">({{$module->activities->count()}} atividade(s))</span></summary>
      <div class="p-2">
      @foreach($module->activities as $activity)
       @php $available = $module->numeroModulo==1 && $activity->numeroAtividade==1 && in_array(strtolower($activity->nomeMateria),['libras','portugues']); @endphp
       <label class="taruga-choice d-flex align-items-center gap-2 {{$available?'':'text-muted'}}"><input type="checkbox" name="activities[]" value="{{$activity->id}}" @checked(in_array($activity->id,old('activities',[]))) @disabled(!$available)><span>{{$activity->nome}} <small class="text-muted">· Atividade {{$activity->numeroAtividade}} {{$available?'':'· Jogo ainda não implementado'}}</small></span></label>
      @endforeach
      </div>
     </details>
     @endif
    @empty <p class="alert alert-warning">Ainda não há atividades cadastradas.</p> @endforelse
   </div>
   <h3 class="h5">Para quem será a avaliação?</h3>
   <fieldset>
    <legend class="visually-hidden">Tipo de atribuição</legend>
    <label class="me-3"><input type="radio" name="assignment_mode" value="class" @checked(old('assignment_mode','class')==='class')> Turma inteira</label>
    <label><input type="radio" name="assignment_mode" value="students" @checked(old('assignment_mode')==='students')> Alunos específicos</label>
   </fieldset>
   <div id="assignment-class" class="mt-3"><label for="target-class" class="form-label">Selecionar turma</label><select id="target-class" name="class_id" class="form-select"><option value="">Selecione uma turma</option>@foreach($classes as $class)<option value="{{$class->id}}" @selected(old('class_id')==$class->id)>{{$class->nome}} — {{$class->serie}} · {{$class->students_count}} aluno(s)</option>@endforeach</select></div>
   <div id="assignment-students" class="mt-3" hidden><label class="form-label" for="student-search">Pesquisar aluno (nome, RA ou turma)</label><input type="search" id="student-search" class="form-control mb-2" placeholder="Ex.: Maria · Turma A"><div class="taruga-scroll-select" id="student-options">
     @forelse($students as $student)<label class="taruga-choice d-flex gap-2 align-items-center" data-search="{{mb_strtolower($student->nome.' '.$student->ra.' '.($student->class?->nome??''))}}"><input type="checkbox" name="students[]" value="{{$student->id}}" @checked(in_array($student->id,old('students',[])))><span><strong>{{$student->nome}}</strong> <span class="text-muted">· {{ $student->class?->nome ?? 'Sem turma' }} · RA {{$student->ra}}</span></span></label>@empty<p>Cadastre alunos antes de criar uma avaliação.</p>@endforelse
    </div></div>
   <button class="btn btn-success mt-4" @disabled($students->isEmpty() || $availableActivityCount===0)>Criar e atribuir avaliação</button>
  </form>
 </div>
 <h2>Minhas avaliações</h2>
 @forelse($assessments as $assessment)
 <div class="taruga-surface mb-3"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2"><h3 class="h4 mb-1">{{$assessment->titulo}}</h3><span class="badge {{$assessment->ativa?'bg-success':'bg-secondary'}}">{{$assessment->ativa?'Aberta':'Finalizada'}}</span></div>
  <p>{{$assessment->descricao}}</p><p>{{$assessment->activities->count()}} atividade(s) · {{$assessment->students->count()}} aluno(s) @if($assessment->prazo) · Prazo: {{$assessment->prazo->format('d/m/Y H:i')}} @endif</p>
  <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-success" href="{{route('teacher.assessments.results',$assessment->id)}}">Resultados e recuperação</a>
   <form method="POST" action="{{route('teacher.assessments.status',$assessment->id)}}">@csrf @method('PATCH')<input type="hidden" name="ativa" value="{{$assessment->ativa?0:1}}"><button class="btn btn-outline-primary">{{$assessment->ativa?'Marcar como finalizada':'Reabrir avaliação'}}</button></form>
   <form method="POST" action="{{route('teacher.assessments.destroy',$assessment->id)}}" onsubmit="return confirm('Excluir definitivamente esta avaliação e todas as notas vinculadas a ela? Esta operação não pode ser desfeita.')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Excluir avaliação</button></form>
  </div>
 </div>
 @empty <div class="taruga-surface">Você ainda não criou avaliações.</div> @endforelse
</div>
<script>
(function(){
 const choice=document.querySelectorAll('input[name="assignment_mode"]');
 function toggle(){let specific=document.querySelector('input[name="assignment_mode"]:checked')?.value==='students';
  document.getElementById('assignment-class').hidden=specific;document.getElementById('assignment-students').hidden=!specific;
  document.getElementById('target-class').required=!specific;
  document.querySelectorAll('#student-options input').forEach(e=>e.disabled=!specific);
 }
 choice.forEach(el=>el.addEventListener('change',toggle));toggle();
 const normalize=s=>s.toLocaleLowerCase('pt-BR');
 document.getElementById('student-search').addEventListener('input',function(){let q=normalize(this.value.trim());document.querySelectorAll('#student-options [data-search]').forEach(el=>el.hidden=!normalize(el.dataset.search).includes(q));});
 document.getElementById('activity-search').addEventListener('input',function(){let q=normalize(this.value.trim());document.querySelectorAll('#taruga-activity-groups details[data-search]').forEach(el=>{el.hidden=!normalize(el.dataset.search).includes(q);if(q&& !el.hidden)el.open=true;});});
})();
</script>
@endsection
