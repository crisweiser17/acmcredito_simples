# ACM Crédito Simples

Sistema simples de gestão de clientes e empréstimos em PHP.

## Instalação (pt-BR)

- Requisitos:
  - `PHP 8.1+` com `pdo_mysql`, `curl` e preferencialmente `gd`
  - `Composer`
  - `MySQL/MariaDB` com InnoDB

- Passos:
  1. Clonar o repositório
     ```bash
     git clone https://github.com/crisweiser17/acmcredito_simples
     cd acmcredito_simples
     ```
  2. Instalar dependências
     ```bash
     composer install
     ```
  3. Configurar banco de dados via arquivo de ambiente (por padrão o app usa `staging`)
     - Criar `/.env.staging` com:
       ```
       DB_HOST=localhost
       DB_NAME=sistema_emprestimos
       DB_USER=root
       DB_PASS=
       ```
     - Opcional: `/.env.production` para produção
  4. Subir servidor local
     ```bash
     php -S localhost:8000 -t public
     ```
  5. Criar base e tabelas
     - Acesse `http://localhost:8000/admin/install`
  6. Login
     - Acesse `http://localhost:8000/login`
     - Um usuário "superadmin" é garantido pelo sistema:
       - Usuário: `crisweiser`
       - Senha: `Ccmcw17@`
     - Seeds adicionais podem existir (ex.: `operador1/2/3`), mas o usuário acima é criado/atualizado automaticamente.

- Rotas úteis:
  - `GET /` Dashboard
  - `GET /clientes` Lista de clientes
  - `GET /clientes/novo` Novo cliente
  - `GET /cadastro` Cadastro público (sem autenticação)
  - `GET /emprestimos/calculadora` Criar solicitação de empréstimo
  - `GET /admin/install` Instalação (migrações)

- Ambientes:
  - Padrão: `staging`
  - Troca de ambiente via query/cookie: `?force_env=production` ou `?force_env=staging`

## Installation (en-US)

- Requirements:
  - `PHP 8.1+` with `pdo_mysql`, `curl` and preferably `gd`
  - `Composer`
  - `MySQL/MariaDB` (InnoDB)

- Steps:
  1. Clone the repository
     ```bash
     git clone https://github.com/crisweiser17/acmcredito_simples
     cd acmcredito_simples
     ```
  2. Install dependencies
     ```bash
     composer install
     ```
  3. Configure database via environment file (default app env is `staging`)
     - Create `/.env.staging`:
       ```
       DB_HOST=localhost
       DB_NAME=sistema_emprestimos
       DB_USER=root
       DB_PASS=
       ```
     - Optional: `/.env.production` for production
  4. Start local server
     ```bash
     php -S localhost:8000 -t public
     ```
  5. Create database and tables
     - Visit `http://localhost:8000/admin/install`
  6. Login
     - Visit `http://localhost:8000/login`
     - A "superadmin" user is ensured by the system:
       - Username: `crisweiser`
       - Password: `Ccmcw17@`
     - Additional seeded users may exist (`operador1/2/3`), but the user above is auto-created/updated.

- Useful routes:
  - `GET /` Dashboard
  - `GET /clientes` Clients list
  - `GET /clientes/novo` New client
  - `GET /cadastro` Public registration (no auth)
  - `GET /emprestimos/calculadora` Loan request calculator
  - `GET /admin/install` Installation (migrations)

- Environments:
  - Default: `staging`
  - Switch via query/cookie: `?force_env=production` or `?force_env=staging`

## Licença

Uso interno e experimental.