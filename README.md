# API Fintech - Laravel 12 + Docker + Sanctum

API RESTful desenvolvida com **Laravel 12**, **Docker** e **Laravel Sanctum**, simulando funcionalidades básicas de uma fintech: autenticação, gestão de usuários e transferências entre contas, com suporte a **roles (user/admin)** e **testes automatizados**.

---

## Tecnologias Utilizadas

- **PHP 8.3**
- **Laravel 12**
- **MySQL (Docker)**
- **Laravel Sanctum** (autenticação via token)
- **PHPUnit** (testes Feature automatizados)
- **Docker Compose** para orquestração de containers

---

## Funcionalidades

- Cadastro e login de usuários (`/api/register`, `/api/login`)
- Autenticação com **Bearer Token (Sanctum)**
- Transferências entre usuários autenticados
- Histórico de transações do usuário
- Listagem geral de transações para **admins**
- Proteção de rotas com **middleware isAdmin**
- Testes automatizados de autenticação e transações

---

## Instalação com Docker

### - Clonar o repositório
```bash
git clone https://github.com/JhonataSnts/api-fintech.git
cd api-fintech

- Subir os containers:

docker compose up -d --build

- Instalar dependências:

docker compose exec app composer install

- Copiar o arquivo de ambiente

cp .env.example .env

- Gerar chave da aplicação:

docker compose exec app php artisan key:generate

- Rodar as migrações:

docker compose exec app php artisan migrate

 Testes Automatizados:

O projeto possui testes de autenticação e transações.

Rodar testes: php artisan test:

Configuração usada para testes (.env.testing):

Banco SQLite em memória

Drivers array para cache e sessão
