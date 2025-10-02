# Sistema de Gestão para Clínica Veterinária (PHP + MySQL + Bootstrap 5)

Aplicação web em PHP puro com PDO (prepared statements), MySQL/MariaDB e Bootstrap 5.

## Requisitos
- PHP 8+
- MySQL 5.7+ ou MariaDB
- Apache/Nginx apontando para `public/` como webroot (ou use o caminho `/public` na URL)
- Extensões PHP: pdo_mysql, fileinfo, mbstring

## Estrutura de Pastas
```
/public
  index.php
  assets/css/
  assets/js/
/src
  controllers/
  models/
  views/
  helpers/
  middlewares/
config.php
router.php
sql/schema.sql
sql/seed.sql
README.md
checklist_deploy.md
```

## Instalação
1. Clone/Copie o projeto para o servidor web (ex.: `c:/xampp/htdocs/veterinaria`).
2. Configure o webroot para a pasta `public/`. Se não for possível, **defina APP_URL com `/public`** no final, por exemplo: `http://localhost/veterinaria/public` em `config.php`.
3. Crie o banco:
   - Importe `sql/schema.sql`.
   - Importe `sql/seed.sql` para dados de exemplo.
4. Ajuste `config.php` com credenciais do banco (DB_HOST, DB_NAME, DB_USER, DB_PASS) e defina `APP_URL`.
5. Garanta permissões de escrita nas pastas: `uploads/` e `logs/`.

## Funcionalidades já implementadas
- Autenticação (login/logout) com `password_hash`/`verify`.
- Controle de sessão, CSRF token e escaping com `htmlspecialchars`.
- Perfis e autorização por rota: `admin`, `veterinario`, `recepcao`, `financeiro`.
- CRUD de Clientes e Pets.
- Agenda com verificação de conflito por veterinário e sala.
- Prontuário por pet (registro de atendimento) e baixa automática de estoque para produtos físicos usados.
- Produtos e Estoque (entrada/saída manual, alerta de estoque crítico).
- PDV/Vendas: adicionar itens, calcular total, registrar pagamento, gerar recibo (HTML imprimível).
- Auditoria de ações (create/update/delete/login/logout).

## Rotas principais
- GET ` /login`, POST ` /login`, GET ` /logout`
- GET ` /dashboard`
- GET ` /clients`, POST ` /clients/create`, POST ` /clients/{id}/edit`, POST ` /clients/{id}/delete`
- GET ` /pets`, POST ` /pets/create`, POST ` /pets/{id}/edit`, POST ` /pets/{id}/delete`
- GET ` /agenda`, POST ` /agenda/create`, POST ` /agenda/{id}/move`, POST ` /agenda/{id}/cancel`
- GET ` /records/{pet_id}`, POST ` /records/{pet_id}/create`
- GET ` /products`, POST ` /products/create`, POST ` /stock/entry`, POST ` /stock/exit`
- GET ` /sales/pos`, POST ` /sales/checkout`

Observação: se `APP_URL` terminar com `/public`, as rotas estarão disponíveis sob esse prefixo.

## Segurança
- Todas as queries via PDO com prepared statements e placeholders nomeados.
- Saída sempre escapada com `e()` (helper) usando `htmlspecialchars`.
- CSRF tokens incluídos em formulários sensíveis e validados.
- Uploads (para próximos recursos): validação de MIME/EXT, tamanho e renomeio com hash.
- Passwords com `password_hash()` e `password_verify()`.

## Testes manuais recomendados
1. Conflito na agenda: tente criar duas consultas para o mesmo vet e sala com intervalos sobrepostos.
2. Atendimento: registre um prontuário usando produtos físicos (ex.: `2:1;4:2`), verifique a baixa no estoque em `products`.
3. PDV: finalize venda com itens físicos e serviço; verifique baixa apenas para itens físicos e recibo impresso.
4. Permissões: tente acessar rotas com usuário de perfil restrito e espere erro 403/redirect.

## Export/Import e Relatórios
- Páginas e endpoints de importação CSV e exportação CSV/PDF, relatórios por período, e gestão de usuários/configurações podem ser adicionados posteriormente. A base de dados e a arquitetura já suportam essas expansões.
- Para PDF, recomenda-se gerar HTML e imprimir (ou usar `wkhtmltopdf`/biblioteca PHP conforme necessidade).

## Dicas
- Ajuste `APP_ENV` para `production` ao publicar. Erros serão logados em `logs/php-errors.log`.
- Timezone: America/Sao_Paulo. UI usa datas `dd/mm/yyyy` (ajuste nos inputs conforme necessidade); banco usa `YYYY-MM-DD`.

## Backup
- Recomenda-se configurar um cron/Task Scheduler para `mysqldump` periódico do banco.
