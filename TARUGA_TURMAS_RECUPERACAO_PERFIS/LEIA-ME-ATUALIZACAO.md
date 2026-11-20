# Taruga — revisão de avaliações, turmas, recuperação e perfis

## Base e preservação

Esta revisão foi feita diretamente sobre `TARUGA_REVISAO_FLUXOS_PERFIS.zip`. Foram preservados os jogos, as imagens, a identidade visual e os arquivos CSS originais. O `database/database.sqlite` empacotado não foi alterado. A nova migração adiciona os campos necessários sem recriar tabelas existentes.

## Funcionalidades alteradas

### Professor

- Removido o botão roxo redundante **Registrar Alunos** da lista de alunos; o acesso **Cadastrar aluno** continua disponível no menu e no botão separado.
- Página inicial com quantidade de turmas, alunos, avaliações, média geral das notas registradas e links para a média por turma.
- Desempenho filtrável por **Todas as turmas** ou por uma turma específica; os cartões dos alunos ainda abrem o histórico completo individual.
- Criação de avaliação com **data e hora em campos separados**, escolhidos no horário configurado na aplicação.
- Atribuição por **turma inteira** ou por **alunos específicos**, com busca por nome, RA e turma; os nomes são acompanhados da turma a que pertencem.
- Biblioteca de atividades agrupada por matéria e módulo, com busca. Atividades sem jogo implementado são mostradas desabilitadas para evitar provas impossíveis de corrigir.
- Ações de **Finalizar/Reabrir** a avaliação, **Excluir** (com confirmação e exclusão de resultados associados), e **Liberar recuperação individual** para um aluno, escolhendo repetir a prova original ou uma atividade alternativa implementada.
- Relatório de avaliação mostra média dos alunos que concluíram a tentativa atual e situação por aluno, com ação de recuperação.

### Aluno

- **Prática** substitui o item **Atividades** na navegação.
- Avaliações possuem estado de conclusão e mostram a tentativa atual. Não permitem nova execução de uma atividade já enviada, mesmo pelo link direto.
- Uma recuperação autorizada pelo professor cria uma nova tentativa individual, preservando a tentativa anterior no histórico.
- Tela de parabéns exibe **Avaliação finalizada!** ao concluir a última atividade da tentativa e oferece acesso direto ao progresso e notas.
- Resultados e histórico organizados em Libras e Português, com atividades livres separadas das provas e horário formatado para `America/Sao_Paulo`.
- Perfil com foto escolhida no próprio computador, progresso separado por matéria e adesivos de conquista. Fotos novas são gravadas em `public/uploads/profiles`, sem depender da criação de symlink de `public/storage`.

### Usuário independente

- Nova página de perfil com foto, progresso por Libras/Português, conquistas e acesso ao histórico; sem dados de professor ou turma.

## Segurança e registros

- Todas as alterações de avaliação são limitadas ao professor proprietário da avaliação.
- A atividade só pode ser enviada na tentativa atual de um aluno atribuído, dentro do prazo (exceto recuperação explicitamente liberada) e enquanto a prova estiver aberta.
- A tentativa única é verificada no backend. O campo `submission_key` tem índice único para proteger novos envios simultâneos, sem alterar resultados históricos que já existirem.
- O histórico de tentativas anteriores é preservado ao liberar recuperação. Excluir uma avaliação, por decisão funcional, remove também suas atribuições e resultados; a interface solicita confirmação.

## Instalação / atualização sem apagar cadastros

1. Extraia o novo ZIP em uma **nova pasta**.
2. Faça backup do `.env` e de `database/database.sqlite` de sua versão atual. Copie-os para a mesma localização relativa dentro do novo `htdocs`.
3. Se já personalizou fotos no novo sistema, copie também a pasta `public/uploads/profiles` da sua instalação anterior para o novo projeto.
4. Dentro da pasta `htdocs` execute:

   ```powershell
   composer install
   php artisan migrate
   php artisan optimize:clear
   php artisan serve
   ```

5. Não execute `migrate:fresh`, `migrate:refresh` nem substitua seu banco com o arquivo do ZIP.
6. No `.env`, confira `APP_TIMEZONE=America/Sao_Paulo`, `DB_CONNECTION=sqlite` e **não deixe** `DB_DATABASE=database/database.sqlite` como caminho relativo. A versão nova do `.env.example` já omite essa linha.
7. Se uma migração falhar, **não limpe as tabelas**: copie o erro do PowerShell para investigação.

## Verificações efetuadas

- `php -l` executado nos 44 arquivos PHP em app, routes e migrations: 0 erros.
- `node --check` executado nos 14 arquivos JavaScript públicos: 0 erros.
- Conferência estática dos nomes de rotas referenciados nas páginas alteradas: sem links para nomes ausentes.
- O arquivo SQLite do ZIP de origem foi preservado sem alterações.

**Limitação:** não foi possível realizar testes de integração HTTP, abrir o site no navegador ou executar `php artisan migrate` neste ambiente, porque as dependências Composer não estão instaladas e o PHP disponível aqui não tem driver SQLite (`pdo_sqlite`). Portanto, a funcionalidade em tempo de execução precisa ser validada localmente. Os jogos também dependem do seu navegador e dos recursos remotos usados pelo projeto.

## Roteiro de validação sugerido

1. Professor cria turma e cadastra um aluno nela.
2. Professor cria uma avaliação com atividade implementada, prazo com data e hora, atribuição para a turma ou aluno específico.
3. Aluno entra e executa a prova. Conclui, consulta o histórico e tenta entrar de novo na mesma atividade — deve ser bloqueado.
4. Professor consulta média e libera recuperação para apenas aquele aluno; aluno realiza a nova tentativa e observa ambas no histórico.
5. Professor finaliza a avaliação; aluno não pode iniciar outra atividade nela.
6. Professor pode excluir uma avaliação de teste: ela desaparece dos alunos e os resultados associados são removidos.
7. Usuário comum e aluno atualizam a foto e verificam as porcentagens de Libras e Português no perfil.
8. Confirme data/hora nas notas; se houver dados anteriores gravados sob timezone diferente, as horas históricas podem precisar de correção específica.
