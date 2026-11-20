# Taruga — relatório de auditoria e correções

**Pacote:** `TARUGA_CORRIGIDO_PARA_TESTE.zip`  
**Base técnica:** Laravel 11, PHP 8.2+, SQLite  
**Escopo:** recuperação do backend e autenticação, com preservação dos arquivos visuais existentes.

## 1. Resultado geral

Foi aplicada uma primeira rodada de recuperação sobre o projeto enviado, sem reconstruí-lo. Foram corrigidas inconsistências entre Models e tabelas, fluxo de autenticação dos quatro perfis, proteção das rotas, vínculos de cadastro e persistência do progresso. Os arquivos de CSS, JavaScript visual, imagens, vídeos e estrutura das páginas foram mantidos; as alterações em Blade foram pontuais e relacionadas a formulários, navegação e perfil exibido.

**Importante:** o pacote está preparado para teste local, mas não posso classificá-lo como integralmente validado em execução. O ambiente usado nesta auditoria não possui `pdo_sqlite`, `sqlite3`, `dom`, `mbstring`, `xml` e `xmlwriter`; por isso não foi possível executar as migrations Laravel nem os testes PHPUnit/HTTP. Os testes estáticos e verificações que o ambiente permitiu estão discriminados abaixo.

## 2. Principais problemas encontrados

1. Vários Models procuravam tabelas no singular (`student`, `teacher`, `institution`, `activity`, `module`, `progress`), enquanto as migrations criam tabelas no plural (`students`, `teachers`, `institutions`, `activities`, `modules`, `progress`).
2. Os Models `Student`, `Teacher` e `Institution` não declaravam de forma consistente o campo de senha utilizado pela autenticação (`senha`).
3. O fluxo de logout e a identificação do usuário dependiam do guard padrão `web`, embora existissem guards separados para aluno, professor e instituição.
4. O cadastro público de “aluno” tentava utilizar uma estrutura que também exige professor e turma. A regra implementada agora diferencia conta independente de aluno institucional.
5. O cadastro de aluno não atribuía turma e professor de maneira consistente, apesar de esses vínculos serem obrigatórios no SQLite.
6. Os Controllers de professores e alunos não restringiam de forma suficiente todas as operações aos registros vinculados à instituição ou ao professor autenticado.
7. A tabela `progress` contém `status`, `fk_estudantes_id` e `fk_atividades_id`; o Model anterior também esperava campos que não existem nessa tabela.
8. Havia uma rota duplicada para a tela da primeira atividade e endpoints de progresso sem a mesma proteção de autenticação das telas.
9. RA, RP, CNPJ e telefone estavam definidos como tipos numéricos, embora sejam identificadores. Isso não preserva zeros à esquerda e pontuação.
10. O cadastro de professor marcava a senha como provisória, mas não havia tela Blade de troca obrigatória de senha conectada ao fluxo de login.

## 3. Correções aplicadas

### Autenticação e sessão

- Mantidos os guards existentes (`web`, `student`, `teacher` e `institution`), evitando uma troca ampla de arquitetura.
- Criado `ResolveAuthGuard` para selecionar o guard da sessão antes da autorização das rotas.
- Login independente, aluno, professor e instituição passam a verificar as credenciais e iniciar a sessão do guard correto.
- Logout centralizado encerra as sessões dos quatro guards, invalida a sessão e renova o token CSRF.
- Redirecionamento de visitantes não autenticados para a tela de identificação.
- Senhas criadas pelos Controllers são armazenadas com hash.

### Cadastro e relacionamentos

- Conta pública independente é armazenada em `users`.
- Instituição cria professores vinculados à própria instituição.
- Professor cria alunos vinculados a si e a uma turma sua.
- Quando o professor ainda não possui turma, o cadastro cria a turma inicial **“Turma geral”** para atender à chave estrangeira obrigatória já existente. Não foi criada uma nova tela de gerenciamento de turmas.
- Consultas, edição, exclusão e pesquisa de professores/alunos são limitadas ao responsável autenticado.
- Exclusão de aluno com progresso e exclusão de professor com alunos/turmas são bloqueadas para preservar o histórico e os relacionamentos.
- A instituição do aluno é obtida pelo relacionamento aluno → professor → instituição; a tabela `students` original não possui uma coluna direta para instituição.

### Banco e progresso

- Ajustadas as tabelas declaradas nos Models e os relacionamentos Eloquent.
- Corrigido o Model `Progress` para corresponder às colunas reais da tabela `progress`.
- Criada a tabela `user_progress` para registrar o progresso das contas independentes sem remover a chave estrangeira legada de `progress`, que aponta para `students`.
- Criadas duas migrations corretivas: uma para tipos de identificadores/flag de senha e índices únicos; outra para `user_progress`.
- As migrations antigas não foram renomeadas nem substituídas, preservando o histórico de 10 migrations já registrado no SQLite recebido.
- Corrigidos o avanço e a consulta de progresso para as telas já existentes de Libras e Português.

### Rotas e interface

- Removida a duplicidade de nome/URL da primeira atividade; a tela de congratulações ganhou uma rota própria.
- Rotas de progresso protegidas por autenticação de aluno ou usuário independente.
- CRUD de alunos protegido por `auth:teacher`; CRUD de professores por `auth:institution`.
- Links de cadastro público direcionam para usuário independente ou instituição. Aluno e professor institucionais são criados pelo responsável, conforme a regra do sistema.
- Ajustados campos de formulário e nomes de rota necessários ao backend; não foi feita reformulação visual.

## 4. Arquivos alterados ou adicionados

### PHP — Controllers, Models e middleware

- `app/Helpers/AuthHelper.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/InstitutionController.php`
- `app/Http/Controllers/ProgressController.php`
- `app/Http/Controllers/StudentAuthController.php`
- `app/Http/Controllers/SupportController.php`
- `app/Http/Controllers/TeacherController.php`
- `app/Http/Middleware/VerifyUpdatedPassword.php`
- `app/Http/Middleware/ResolveAuthGuard.php` *(novo)*
- `app/Models/Activity.php`
- `app/Models/ClassModel.php`
- `app/Models/Institution.php`
- `app/Models/Module.php`
- `app/Models/Progress.php`
- `app/Models/Student.php`
- `app/Models/Teacher.php`
- `app/Models/User.php`
- `app/Models/UserProgress.php` *(novo)*
- `bootstrap/app.php`
- `routes/web.php`

### Banco e testes

- `database/migrations/2026_11_18_000001_correct_taruga_auth_columns.php` *(novo)*
- `database/migrations/2026_11_18_000002_create_user_progress_table.php` *(novo)*
- `tests/Feature/TarugaAuthenticationTest.php` *(novo)*

### Views e configuração

- `resources/views/homeUser.blade.php`
- `resources/views/identification.blade.php`
- `resources/views/layouts/headerFooter.blade.php`
- `resources/views/login.blade.php`
- `resources/views/register.blade.php`
- `resources/views/usersList.blade.php`
- `.env.example`
- `README.md`

## 5. Banco SQLite recebido

O arquivo `database/database.sqlite` foi preservado sem alterações. Foi comparado por SHA-256 com o membro correspondente do ZIP original e os hashes coincidiram.

Na auditoria somente leitura, o SQLite continha 10 migrations originais registradas. As tabelas de dados de aplicação (`users`, `institutions`, `teachers`, `students`, `classes`, `modules`, `activities` e `progress`) estavam vazias no arquivo recebido. A tabela `sessions` continha duas linhas. Portanto, não havia cadastros pedagógicos a migrar no banco enviado.

As duas migrations novas estão pendentes e devem ser executadas localmente com `php artisan migrate`. Não execute `migrate:fresh` nem apague o SQLite. Faça uma cópia de segurança antes de migrar qualquer banco que já tenha cadastros em outro ambiente.

## 6. Testes e verificações realizados

| Verificação | Resultado |
|---|---|
| Sintaxe PHP em `app`, `routes`, migrations e testes | Aprovada (`php -l`) |
| Registro das rotas Laravel | Aprovado — 51 rotas listadas |
| Compilação direta das Views Blade | Aprovada — 30 arquivos compilados |
| Inspeção de tabelas, colunas e contagens do SQLite | Concluída em modo somente leitura |
| Integridade do arquivo SQLite original | SHA-256 idêntico ao ZIP de entrada |
| PHPUnit / testes Feature | Não executados — extensões PHP ausentes |
| Migrations Laravel contra SQLite | Não executadas — `pdo_sqlite`/`sqlite3` ausentes |
| Testes HTTP, navegador e interação visual | Não executados neste ambiente |

Os testes Feature adicionados cobrem cadastro/login de usuário independente, autenticação de aluno, criação e login de professor pela instituição e tentativas de acesso entre perfis. Eles precisam ser executados no computador de desenvolvimento depois de habilitar as extensões indicadas.

## 7. Limitações e pontos que ainda precisam de atenção

- Não consegui validar a execução real do banco, migrations ou login HTTP neste ambiente por falta das extensões PHP indicadas. A primeira validação local deve ser `php artisan migrate` seguida de `php artisan test`.
- Não encontrei, na estrutura existente, uma tela/Controller completo para relatório de notas e desempenho do professor ou painel institucional agregado de alunos. O registro de progresso foi corrigido, mas não foi inventado um novo painel pedagógico.
- O modelo original não possui relacionamento direto entre aluno e instituição; a instituição é determinada pelo professor responsável.
- Não encontrei uma tela de alteração obrigatória da senha provisória do professor. Para evitar bloquear o professor após o login, o cadastro não força essa etapa no momento. A rota protegida de atualização de senha foi corrigida, mas a interface para o fluxo obrigatório ainda precisa ser definida.
- O envio real do formulário de suporte depende de configurar SMTP no `.env`. O `.env.example` usa `MAIL_MAILER=log` para desenvolvimento local.
- As atividades e jogos já existentes foram preservados; não foram criados módulos ou exercícios novos.

## 8. Como instalar e testar

Execute os comandos na pasta `TARUGA_V1.0/Taruga-laravel/htdocs`.

1. Instale PHP 8.2+ com `pdo_sqlite`, `sqlite3`, `mbstring`, `dom`, `xml`, `xmlwriter`, `fileinfo`, `openssl` e `ctype`, além de Composer 2. Node.js/npm são necessários para recompilar os assets, não para servir os assets já existentes.
2. Copie `.env.example` para `.env` e gere uma chave:

   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   ```

   No PowerShell, use `Copy-Item .env.example .env` no lugar de `cp`.

3. Confirme no `.env`:

   ```dotenv
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

4. Aplique somente as migrations pendentes:

   ```bash
   php artisan migrate
   ```

5. Inicie a aplicação:

   ```bash
   php artisan serve
   ```

   Acesse `http://127.0.0.1:8000/identification`.

6. Para recompilar os arquivos de frontend, quando necessário:

   ```bash
   npm install
   npm run build
   ```

### Roteiro de teste manual dos quatro perfis

1. **Usuário independente:** em identificação, selecione Usuário, cadastre nome/e-mail/senha, depois entre e abra Atividades.
2. **Instituição:** cadastre uma instituição, entre com o e-mail e senha dela e acesse a área de professores. Cadastre um professor com RP, e-mail e senha.
3. **Professor:** saia da instituição, entre como professor com o e-mail e senha cadastrados. Abra Turmas/Alunos e cadastre um aluno com RA, e-mail, senha e situação.
4. **Aluno:** saia do professor, entre como aluno com o e-mail e senha cadastrados e acesse as atividades.
5. **Permissões:** tente acessar diretamente `/institution/List` como professor/aluno, `/teacher/List` como instituição/aluno e `/student/Profile` como usuário independente. O acesso deve ser negado ou redirecionado.
6. **Logout:** saia de cada perfil e confirme que voltar à URL anterior não reabre o painel autenticado.
7. **Progresso:** entre como usuário ou aluno, abra os módulos de Libras e Português, inicie a atividade e avance algumas etapas. O progresso de usuário independente fica em `user_progress`; o de aluno institucional fica em `progress`.

Depois execute:

```bash
php artisan test
```

## 9. Segurança do pacote

O ZIP de devolução não inclui `.env`, `vendor/`, `node_modules/` nem `.git/`. Inclui `.env.example`, arquivos de dependências, migrations, código-fonte, assets, documentação e o SQLite original. Gere sua própria chave de aplicação e configure credenciais locais antes de publicar ou expor a aplicação.
