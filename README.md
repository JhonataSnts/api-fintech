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

## Resultados dos Testes e Demonstrações:

<img width="931" height="63" alt="Captura de tela de 2025-11-06 17-05-49" src="https://github.com/user-attachments/assets/7ca30386-d032-4c94-bddd-2653ee88216f" />

<img width="500" height="600" alt="testes feature" src="https://github.com/user-attachments/assets/6125a973-bbfe-4bcd-b319-9e20b9036dfb" />

<img width="500" height="627" alt="api_login" src="https://github.com/user-attachments/assets/9303cb24-0d99-4299-b206-366de8db8018" />

<img width="500" height="621" alt="transactions" src="https://github.com/user-attachments/assets/6a4012a9-7ced-4c01-8753-4b9b11371600" />

<img width="500" height="621" alt="get_transactions" src="https://github.com/user-attachments/assets/c380c118-e5b3-4671-bf37-54764f74182c" />

<img width="500" height="573" alt="admin_transactions_all" src="https://github.com/user-attachments/assets/0d274b2d-e174-4269-9c70-499c0fae2514" />

<img width="500" height="621" alt="update" src="https://github.com/user-attachments/assets/54f44354-2f36-43fe-9274-b648baf8d7c6" />

<img width="500" height="621" alt="delete" src="https://github.com/user-attachments/assets/c5fb671a-cecd-45c9-b957-ae8e62f5a632" />

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
