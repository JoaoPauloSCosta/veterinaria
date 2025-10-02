# Checklist de Deploy

- [ ] Instalar PHP 8+, Apache/Nginx, MySQL 5.7+/MariaDB
- [ ] Habilitar extensões PHP: pdo_mysql, fileinfo, mbstring
- [ ] Clonar o projeto em `c:/xampp/htdocs/veterinaria` (Windows/XAMPP) ou `/var/www/veterinaria` (Linux)
- [ ] Definir webroot para `public/` (Apache: DocumentRoot; Nginx: root). Caso não seja possível, ajuste `APP_URL` no `config.php` para conter `/public` ao final
- [ ] Criar banco de dados e importar `sql/schema.sql`
- [ ] Importar `sql/seed.sql` para dados iniciais (admin, exemplos)
- [ ] Configurar `config.php` (DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL, APP_ENV)
- [ ] Garantir permissões de escrita: `uploads/` e `logs/`
- [ ] Ativar HTTPS e configurar `session.cookie_secure = 1` em produção
- [ ] Opcional: configurar virtual host (Apache) ou server block (Nginx)
- [ ] Configurar backup: cron/Task Scheduler rodando `mysqldump` diário
- [ ] Testes pós-deploy:
  - [ ] Login do admin (`admin@clinic.local` / `Admin@123`)
  - [ ] CRUD clientes/pets
  - [ ] Agendamento com verificação de conflito
  - [ ] Prontuário com baixa de estoque
  - [ ] PDV com pagamento e recibo imprimível
  - [ ] Permissões por perfil

## Segurança
- [ ] `APP_ENV=production` com exibição de erros desativada e logs habilitados
- [ ] Usar senha forte para DB e admin
- [ ] Uploads restritos a `jpg/jpeg/png/pdf`, renomear com hash, validar MIME
- [ ] CSRF habilitado e checado nos POSTs
- [ ] `password_hash`/`password_verify` para senhas
- [ ] Revisar permissões de arquivos/diretórios (mínimo necessário)
