# Manual de Execução do Projeto

## Pré-requisitos

Antes de executar o sistema, verifique se os seguintes programas estão instalados no computador:

* PHP (INSTALAR NO PC)
-- winget install --id PHP.PHP.8.5 -e /ou/ php artisan serve
-- winget search php

**Scoop (INSTALAR NO PC)
-- Set-ExecutionPolicy RemoteSigned -Scope CurrentUser
-- irm get.scoop.sh | iex

* Composer (INSTALAR NO PC)
-- winget install --id Composer.Composer -e /ou/ composer install ** n pego
-- winget search composer
-- scoop install composer (instalando com scooper)!
-- composer -V

* Banco de dados compatível com o projeto (caso necessário)

## Passo a passo

1. Extraia os arquivos do projeto e acesse a pasta:

```
C:\Users\User\Documents\GitHub\TARUGA\TARUGA_V1.0\Taruga-laravel\htdocs
```

2. Abra um terminal nessa pasta.

3. Instale as dependências do projeto:

```bash
composer install
```

4. Crie o arquivo de configuração do ambiente:

```bash
copy .env.example .env
```

5. Gere a chave da aplicação:

```bash
php artisan key:generate
```

6. Caso o banco de dados ainda não esteja configurado, execute as migrações:

```bash
php artisan migrate
```

7. Inicie o servidor da aplicação:

```bash
php artisan serve
```

8. Abra um navegador e acesse o endereço:

```
http://127.0.0.1:8000
```

Após esses passos, o sistema estará disponível para utilização.
