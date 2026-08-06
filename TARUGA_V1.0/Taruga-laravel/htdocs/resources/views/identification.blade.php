@extends('layouts.headerFooter')

@section('title', 'Identificação')

@push('styles')
    <link rel="stylesheet" href="telaIdent.css" />
    <link rel="stylesheet"href="https://fonts.googleapis.com/css?family=Jost"/>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href=" {{ asset('css/identification.css')}}?v={{ time() }}">
    <link href="https://fonts.googleapis.com/css2?family=Jersey+10&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
    <!--Centro página-->
    
    <div class="alignCenter">
      <div class="container-bemVindo">
        <div class="header-conteiner">
          <!-- Seta -->
          <div class="seta">
            <a onclick="window.history.back()">
              <i class="iconSeta bi bi-arrow-left-circle-fill text-white fs-1"></i>
            </a>
          </div>
          <div class="BemVindoP">
            <p style="font-weight: 130; line-height: 0.8; font-size: 50px; margin: 0px; padding: 0px;">BEM VINDO AO TARUGA!</p>
            <p>NOS INFORME QUEM VOCÊ É:</p>
          </div>
        </div>
            <div class="pais">
              <div class="professor">
                <div class="align-img">
                   <img
                      src="{{ asset('storage/imgs/Professora_TelaIdent.png') }}"
                      alt="Imagem de uma professora"
                      style="width: auto; height: 160px; margin-bottom: 10px;"
                   />
                </div>
                <div class="align-p">
                    <p>Professor(a)</p>
                </div>
                <div class="align-button">
                  <a href="{{ route('choose-login', 'teacher') }}" class="btn">Entrar</a>
                </div>
            </div>

            <div class="aluno">
              <div class="align-img">
                <img
                  src="{{ asset('storage/imgs/Bolsa_TelaIdent.png') }}"
                  alt="Imagem de uma bolsa escolar"
                  style="width: auto; height: 160px; margin-bottom: 10px;"
                />
              </div>
              <div class="align-p">
                <p>Aluno(a)</p>
              </div>
              <div class="align-button">
                <a href="{{ route('choose-login', 'student') }}" class="btn">Entrar</a>
              </div>
            </div>

            <div class="instituicao">
              <div class="align-img">
                <img
                  src="{{ asset('storage/imgs/Escola_TelaIdent.png') }}"
                  alt="Imagem de uma instituição escolar"
                  style="width: 150px; height: 160px; margin-bottom: 10px;"
                />
              </div>
              <div class="align-p">
                <p>Instituição</p>
              </div>
              <div class="align-button">
                 <a href="{{ route('choose-login', 'institution') }}" class="btn">Entrar</a>
              </div>
            </div>

            <div class="comunUser">
              <div class="align-img">
                <img
                  src="{{ asset('storage/imgs/User_TelaIdent.png') }}"
                  alt="Foto do simbolo de um usuário"
                  style="width: 150px; height: 160px; margin-bottom: 10px;"
                />
              </div>
              <div class="align-p">
                <p>Usuário(a)</p>
              </div>

              <div class="align-button">
                <a href="{{ route('choose-login', 'student') }}" class="btn">Entrar</a>
              </div>
            </div>
      </div>
      <div class="align-openPopupButton">
        <div class="align-itens-popup-button">
          <div class="div-img-icon-more">
            <img src="{{ asset('storage/imgs/icon_more.png') }}" alt="icone de +">
          </div>
          <div class="div-p-text-cadastro">
            <p style="font-weight: 130; line-height: 0.8; font-size: 25px; margin: 0px; padding: 0px;" class="textoCadastro">Ainda não possui uma cadastro?</p>
            <p class="textoCadastro">Crie sua conta agora mesmo e aproveite tudo que o Taruga oferece!</p>
          </div>
          <div class="div-btn-btnNTC">
            <button id="openPopupButton">
                Criar conta
            </button>
          </div>
        </div>
      </div>

          <!-- Popup -->
<div id="popup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
        <span class="close-button" id="closePopupButton"><img src="{{ asset('storage/imgs/iconx.png') }}"></span>
        <p>Como você gostaria de se cadastrar?</p>
        <button><a href="{{ route('choose-register', 'institution') }}"><img src="{{ asset('storage/imgs/Escola_TelaIdent.png') }}">Instiuição!</a></button>
        <button><a href="{{ route('choose-register', 'student') }}"><img src="{{ asset('storage/imgs/User_TelaIdent.png') }}">Usuário</a></button>
    </div>
</div>

    <script src="{{ asset('js/identification.js') }}"></script>
@endsection
