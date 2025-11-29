# ACM Crédito Simples

![PHP](https://img.shields.io/badge/PHP-%3E%3D%208.1-777bb4?logo=php)
![Composer](https://img.shields.io/badge/Composer-required-orange?logo=composer)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?logo=mysql)
![License](https://img.shields.io/badge/License-Internal-lightgrey)

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

### Deploy Rápido (Docker Compose)

Crie um arquivo `docker-compose.yml`:

```yaml
version: '3.8'
services:
  db:
    image: mysql:8.0
    command: --default-authentication-plugin=mysql_native_password --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
    environment:
      MYSQL_DATABASE: sistema_emprestimos
      MYSQL_USER: app
      MYSQL_PASSWORD: app123
      MYSQL_ROOT_PASSWORD: rootpass
    ports:
      - '3306:3306'
    volumes:
      - dbdata:/var/lib/mysql
  app:
    image: php:8.2-apache
    depends_on:
      - db
    ports:
      - '8080:80'
    volumes:
      - ./:/var/www/html
    environment:
      APACHE_DOCUMENT_ROOT: /var/www/html/public
    command: bash -lc "a2enmod rewrite && apache2-foreground"
volumes:
  dbdata:
```

- Ajuste o arquivo `/.env.production` para usar o banco do Compose:

```
DB_HOST=db
DB_NAME=sistema_emprestimos
DB_USER=app
DB_PASS=app123
```

- Suba os serviços:

```bash
docker compose up -d
```

- Acesse `http://localhost:8080/admin/install` para criar o schema e `http://localhost:8080/login` para entrar.

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

### Quick Deploy (Docker Compose)

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  db:
    image: mysql:8.0
    command: --default-authentication-plugin=mysql_native_password --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
    environment:
      MYSQL_DATABASE: sistema_emprestimos
      MYSQL_USER: app
      MYSQL_PASSWORD: app123
      MYSQL_ROOT_PASSWORD: rootpass
    ports:
      - '3306:3306'
    volumes:
      - dbdata:/var/lib/mysql
  app:
    image: php:8.2-apache
    depends_on:
      - db
    ports:
      - '8080:80'
    volumes:
      - ./:/var/www/html
    environment:
      APACHE_DOCUMENT_ROOT: /var/www/html/public
    command: bash -lc "a2enmod rewrite && apache2-foreground"
volumes:
  dbdata:
```

- Set `/.env.production` to use Compose db:

```
DB_HOST=db
DB_NAME=sistema_emprestimos
DB_USER=app
DB_PASS=app123
```

- Start services:

```bash
docker compose up -d
```

- Visit `http://localhost:8080/admin/install` to migrate and `http://localhost:8080/login` to sign in.

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