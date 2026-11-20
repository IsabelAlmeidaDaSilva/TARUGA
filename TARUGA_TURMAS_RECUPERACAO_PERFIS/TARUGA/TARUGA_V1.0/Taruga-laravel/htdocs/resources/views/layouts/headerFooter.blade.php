<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href=" {{ asset('css/headerFooter.css')}}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/taruga-improvements.css') }}?v=2">
    @stack('styles') <!-- Permite adicionar estilos específicos para cada página -->
</head>
<body>
            <!-- VLIBRAS -->

            <div vw class="enabled">
            <div vw-access-button class="active"></div>
            <div vw-plugin-wrapper>
                <div class="vw-plugin-top-wrapper"></div>
            </div>
        </div>


        <!---------------------------->
     <!-- Header -->
     <nav class="header taruga-nav" aria-label="Navegação principal">
       <div class="container taruga-nav-inner">
         @php
           $role = session('user_type');
           $startRoute = in_array($role,['student','user'],true) ? 'HomeStudent' : ($role==='teacher' ? 'HomeTeacher' : ($role==='institution' ? 'HomeInstitution' : 'home'));
         @endphp
         <a class="taruga-brand" href="{{ route($startRoute) }}" aria-label="Início do Taruga">
           <img src="{{asset('storage/imgs/Taruga_Icon.png')}}" alt="Tartaruguinha" width="52" height="46">
           <img src="{{asset('storage/imgs/Taruga_Logo.png')}}" alt="Taruga" class="taruga-brand-name">
         </a>
         @if($role)
         <details class="taruga-menu" id="taruga-main-menu">
           <summary aria-label="Abrir menu de navegação"><i class="bi bi-list" aria-hidden="true"></i> Menu</summary>
           <ul class="taruga-menu-list">
             <li><a href="{{route($startRoute)}}"><i class="bi bi-house-door"></i> Início</a></li>
             @if(in_array($role,['student','user'],true))
               <li><a href="{{route('SearchSubject')}}"><i class="bi bi-book"></i> Prática</a></li>
               @if($role==='student')
                 <li><a href="{{route('student.assessments')}}"><i class="bi bi-pencil-square"></i> Minhas avaliações</a></li>
               @endif
               <li><a href="{{route('learning.results')}}"><i class="bi bi-graph-up"></i> Resultados e histórico</a></li>
               <li><a href="{{route('learning.achievements')}}"><i class="bi bi-award"></i> Conquistas</a></li>
               <li><a href="{{route('learning.profile')}}"><i class="bi bi-person-circle"></i> Meu perfil</a></li>
               <li><a href="{{route('AboutUsStudent')}}">Sobre nós</a></li>
             @elseif($role==='teacher')
               <li><a href="{{route('teacher.classes')}}"><i class="bi bi-collection"></i> Minhas turmas</a></li>
               <li><a href="{{route('teacher.list')}}"><i class="bi bi-people"></i> Lista de alunos</a></li>
               <li><a href="{{route('teacher.students.create')}}"><i class="bi bi-person-plus"></i> Cadastrar aluno</a></li>
               <li><a href="{{route('teacher.assessments')}}"><i class="bi bi-pencil-square"></i> Avaliações</a></li>
               <li><a href="{{route('teacher.performance')}}"><i class="bi bi-graph-up"></i> Desempenho dos alunos</a></li>
               <li><a href="{{route('AboutUsTeacher')}}">Sobre nós</a></li>
             @elseif($role==='institution')
               <li><a href="{{route('institution.dashboard')}}"><i class="bi bi-speedometer2"></i> Painel da instituição</a></li>
               <li><a href="{{route('institution.list')}}"><i class="bi bi-people"></i> Professores</a></li>
               <li><a href="{{route('AboutUsInstitution')}}">Sobre nós</a></li>
             @endif
             <li><a href="{{route('contact')}}">Contato</a></li>
             <li class="taruga-menu-logout"><form method="POST" action="{{route('logout')}}">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> Sair</button></form></li>
           </ul>
         </details>
         @else
         <a class="btn btn-outline-light" href="{{route('identification')}}">Entrar</a>
         @endif
       </div>
     </nav>
 <!-- Conteúdo Principal -->
 <main>
        @yield('content')
 </main>
 <!-- Rodapé -->
 <footer class="footer">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-3">
                    <h5>Início</h5>
                    <ul class="list-unstyled">
                    @if(in_array(session('user_type'), ['student', 'user'], true))
                    <a class="links" href="{{ route('HomeStudent') }}">
                        <li>Home</li>
                    </a>
                    @elseif(session('user_type') == 'teacher')
                    <a class="links" href="{{ route('HomeTeacher') }}">
                        <li>Home</li>
                    </a>
                    @elseif(session('user_type') == 'institution')
                    <a class="links" href="{{ route('HomeInstitution') }}">
                        <li>Home</li>
                    </a>
                    @endif
                        @if(in_array(session('user_type'), ['student', 'user'], true))
                        <a class="links" href="{{ route('SearchSubject') }}">
                            <li>Prática</li>
                        </a>
                        @elseif(session('user_type') == 'institution')
                        <a class="links" href="{{ route('institution.list') }}">
                            <li>Professores</li>
                        </a>
                        @elseif(session('user_type') == 'teacher')
                        <a class="links" href="{{ route('teacher.list') }}">
                            <li>Alunos</li>
                        </a>
                        @endif
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Sobre nós</h5>
                    <ul class="list-unstyled">
                    @if(in_array(session('user_type'), ['student', 'user'], true))
                    <a class="links" href="{{ route('AboutUsStudent') }}">
                        <li>Empresa</li>
                    </a>
                    @elseif(session('user_type') == 'teacher')
                    <a class="links" href="{{ route('AboutUsTeacher') }}">
                        <li>Empresa</li>
                    </a>
                    @elseif(session('user_type') == 'institution')
                    <a class="links" href="{{ route('AboutUsInstitution') }}">
                        <li>Empresa</li>
                    </a>
                    @endif
                        <a class="links" href="{{ route('contact') }}">
                            <li>Contato</li>
                        </a>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h5>Suporte</h5>
                    <p>beemaysuporte@gmail.com</p>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="social-icons d-flex align-items-center justify-content-center">
                        <a href="#" aria-label="Instagram"><i class="links bi bi-instagram"></i></a>
                        <a href="#" aria-label="Gmail"><i class="links bi bi-envelope-fill"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="links bi bi-linkedin"></i></a>
                    </div>
                    <div class="button d-flex align-items-center justify-content-center">
                        <button class="buttonHover btn btn-contact mt-3"><a class="link-contact" href="{{ route('contact') }}">Contate-nos</a></button>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Fecha o menu quando alguém clica fora ou pressiona Escape.
      document.addEventListener('click', function (event) {
        const menu = document.getElementById('taruga-main-menu');
        if (menu && menu.open && !menu.contains(event.target)) menu.open = false;
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          const menu = document.getElementById('taruga-main-menu');
          if (menu) { menu.open = false; menu.querySelector('summary')?.focus(); }
        }
      });
    </script>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script> new window.VLibras.Widget('https://vlibras.gov.br/app'); </script>

</body>
</html>