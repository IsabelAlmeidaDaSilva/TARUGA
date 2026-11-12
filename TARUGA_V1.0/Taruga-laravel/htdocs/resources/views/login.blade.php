<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ time() }}">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Jost" />
  <link href="https://fonts.googleapis.com/css2?family=Jersey+10&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">


  <title>Login</title>

</head>

<body>
  <!-- Seta -->
  <div class="seta">
    <a onclick="window.history.back()">
      <i class="iconSeta bi bi-arrow-left-circle-fill"></i>
    </a>
  </div>

  <div class="alignDivForm">
    <div class="DivForm">
      <div class="div-DivInf">
        <div class="DivInf">
          <p id="BemVindoTitulo">
            Bem-vindo(a)<br />
            de volta!
          </p>
          <div id="AlignImgDivInf">
            <img
              src="{{ asset('storage/imgs/BemVindos.jpg') }}"
              alt="Imagem de BemVindos" />
          </div>
        </div>
      </div>
      <!--Fim Lado Esquerdo-->

      <div class="alignLog">
        <div id="AlignTituloLogin">
          <h1>LOGIN</h1>
        </div>


        <form action="{{ route('auth.login')}}" method="POST">
          @csrf

          <div id="alignEmail">
            <input type="text" name="email" placeholder="Email" required />
          </div>

          <div id="alignSenha">
            <input type="password" name="senha" placeholder="Senha" required />
          </div>

          <!-- <a href="" class="link-senha"> Esqueci minha senha!</a> -->

          <div id="alignBottomEnviar">
            <input type="submit" name="submit" value="ENTRAR" />
          </div>
        </form>

        @if($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>
    </div>
  </div>

</html>