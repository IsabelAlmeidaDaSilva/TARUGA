<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>{{($assessment && $finished)?'Avaliação finalizada':'Parabéns'}} · Taruga</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"><link rel="stylesheet" href="{{asset('css/congratulations.css')}}?v=4"><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Jost"></head>
<body>
<div class="seta"><a href="{{route('learning.results')}}" aria-label="Ir ao meu progresso">← Resultados</a></div>
<div class="divCentralizaElementos"><div class="containerTrofeu"><img class="imgTrofeu" src="{{asset('storage/app/public/Imgs/TrofeuImg.gif')}}" alt="Troféu"></div></div>
<div style="text-align:center;font-family:Jost,sans-serif;padding:0 1rem"><h1>{{($assessment && $finished)?'Avaliação finalizada!':'Parabéns pela atividade!'}}</h1>
@if($assessment && !$finished)<p>Seu resultado foi salvo. Faltam {{$remaining}} atividade(s) nesta avaliação.</p>
@elseif($assessment)<p>Você concluiu todas as atividades desta tentativa. Somente o professor pode liberar uma recuperação.</p>
@else<p>Sua nota foi registrada e já está disponível no histórico.</p>@endif</div>
<div class="divButton"><a id="continueButton" class="cssButtons" href="{{route('learning.results')}}">Ver meu progresso e minhas notas</a></div>
@if($assessment && !$finished && $assessment->ativa)<div style="text-align:center;margin:1rem"><a href="{{route('student.assessments.show',$assessment->id)}}">Voltar às atividades restantes da avaliação</a></div>@endif
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script><script src="{{asset('js/congratulations.js')}}?v=4"></script>
</body></html>
