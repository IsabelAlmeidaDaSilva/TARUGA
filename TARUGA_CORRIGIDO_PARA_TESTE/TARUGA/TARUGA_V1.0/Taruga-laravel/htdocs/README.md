# Taruga

Plataforma educacional inclusiva para atividades de alfabetização em português e Libras. O projeto utiliza Laravel 11, PHP e SQLite.

## Requisitos

- PHP 8.2 ou superior, com extensões `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo` e `curl` habilitadas.
- Composer 2.
- Node.js e npm (para recompilar os assets, quando necessário).

## Instalação local

Execute os comandos a partir da pasta `htdocs`:

```bash
cp .env.example .env
composer install
php artisan key:generate
```

O arquivo `database/database.sqlite` é mantido no pacote. Para uma instalação nova, crie o arquivo caso ele não exista:

```bash
# Linux/macOS
mkdir -p database
 touch database/database.sqlite

# Windows PowerShell
New-Item -ItemType File -Force database/database.sqlite
```

Configure o SQLite no `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Depois execute as migrations pendentes:

```bash
php artisan migrate
```

Não use `migrate:fresh` em bancos com dados que deseja preservar. As migrations corretivas deste pacote são aditivas ou alteram tipos de identificadores preservando os valores existentes.

Para instalar/compilar os recursos de frontend:

```bash
npm install
npm run build
```

Inicie o servidor:

```bash
php artisan serve
```

Acesse `http://127.0.0.1:8000`.

## Perfis e acesso

- **Usuário independente:** cria a própria conta pelo fluxo “Usuário”; é armazenado em `users` e autentica pelo guard `web`. Pode acessar as atividades sem vínculo institucional.
- **Aluno:** é cadastrado por um professor; pertence ao professor e à turma, e autentica pelo guard `student`.
- **Professor:** é cadastrado por uma instituição; pertence a ela e autentica pelo guard `teacher`.
- **Instituição:** realiza o próprio cadastro e autentica pelo guard `institution`.

Os logins usam a tela `/choose-login/{perfil}`. A página `/identification` apresenta a escolha dos perfis.

## Banco de dados e progresso

As tabelas principais são `users`, `institutions`, `teachers`, `classes`, `students`, `modules`, `activities` e `progress`. O progresso de usuários independentes é armazenado em `user_progress`, separado do progresso dos alunos institucionais para manter a relação com os registros legados sem remover a chave estrangeira existente.

RA, RP, CNPJ e telefone são identificadores/textos; suas migrations corretivas os convertem para colunas de texto para preservar zeros à esquerda e pontuação. Professores e instituições têm acesso limitado aos registros vinculados por suas chaves estrangeiras.

## Testes

Execute:

```bash
php artisan test
```

Os testes de feature em `tests/Feature/TarugaAuthenticationTest.php` cobrem cadastro/login do usuário independente, login de aluno, criação e login de professor, vínculo institucional e tentativas de acesso cruzado.

## Observações

- O arquivo `.env` não deve ser versionado nem compartilhado. Gere uma chave própria com `php artisan key:generate`.
- `vendor/` e `node_modules/` não são distribuídos no ZIP; instale-os com Composer e npm.
- O banco SQLite incluído foi preservado como recebido. Faça uma cópia de segurança antes de executar migrations em ambientes com dados reais.
