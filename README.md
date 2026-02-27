# CRM API

API RESTful para gerenciamento de clientes e endereços, desenvolvida como desafio técnico.

---

## Sumário

- [📖 Descrição do projeto](#-descrição-do-projeto)
- [🏗 Arquitetura escolhida](#-arquitetura-escolhida)
- [🚀 Como rodar o projeto](#-como-rodar-o-projeto)
- [🗄 Configuração do banco](#-configuração-do-banco)
- [🔐 Como autenticar](#-como-autenticar)
- [📬 Exemplos de requests](#-exemplos-de-requests)
- [🧪 Como rodar os testes](#-como-rodar-os-testes)
- [📌 Decisões técnicas](#-decisões-técnicas)

---

## 📖 Descrição do projeto

Sistema que permite a usuários autenticados gerenciar sua própria base de clientes e os endereços associados a cada um. Cada usuário enxerga e manipula apenas seus próprios dados (isolamento por propriedade).

Funcionalidades:
- Registro e autenticação de usuários via JWT
- CRUD completo de clientes (com paginação)
- CRUD completo de endereços, aninhado ao cliente
- Busca automática de dados de endereço via CEP (BrasilAPI)
- Revogação de tokens no logout

---

## 🏗 Arquitetura escolhida

O projeto segue **DDD simplificado** com separação em quatro camadas:

```
app/
├── Domain/          # Regras de negócio puras: Models, Repositories (interfaces)
│   ├── User/
│   ├── Customer/
│   └── Address/
│
├── Application/     # Orquestração: Services que coordenam o domínio
│   └── Services/
│
├── Infrastructure/  # Implementações externas: Gateways HTTP, Repositories Eloquent
│   ├── Gateways/    # BrasilApiCepGateway
│   └── Http/Middleware/
│
└── Presentation/    # Entrada HTTP: Controllers, Requests, Resources
    └── Http/
        ├── Controllers/
        ├── Requests/
        └── Resources/
```

### Padrões aplicados

- **Repository Pattern** — Controllers nunca tocam o banco diretamente; dependem de interfaces
- **Dependency Injection** — todas as dependências resolvidas pelo container do Lumen
- **Gateway Pattern** — integração com BrasilAPI isolada em `CepGatewayInterface`, facilitando mock nos testes
- **Resource / Request Objects** — entrada e saída padronizadas em classes dedicadas
- **TDD** — testes escritos antes da implementação (Red → Green → Refactor)
- **GitFlow** — branches `main`, `develop`, `feature/*`

---

## 🚀 Como rodar o projeto

### Pré-requisitos

- Docker e Docker Compose
- Git

### 1. Clone o repositório

```bash
git clone <repo-url>
cd crm
```

### 2. Configure o ambiente (Verifique as variáveis corretas na seção de Configuração do Banco de Dadoss)

```bash
cp .env.example .env
```

Edite o `.env` e preencha `JWT_SECRET` com uma string aleatória longa:

```bash
# gerar um secret seguro
openssl rand -hex 64
```

### 3. Suba os containers

```bash
docker-compose up -d
```

Isso sobe:
- **crm_app** — Lumen na porta `8000`
- **crm_pgsql** — PostgreSQL 16 na porta `5432`

### 4. Rode as migrations e seeders

```bash
docker-compose exec app php artisan migrate --seed
```

### 5. Acesse a API

```
http://localhost:8000/api/v1
```

---

## 🗄 Configuração do banco

### Desenvolvimento local (PostgreSQL via Docker)

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql        # nome do serviço no docker-compose
DB_PORT=5432
DB_DATABASE=crm
DB_USERNAME=crm_user
DB_PASSWORD=crm_password
```

### Desenvolvimento local (sem Docker)

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/absoluto/para/crm/database/database.sqlite
```

```bash
touch database/database.sqlite
php artisan migrate
```

### Testes (SQLite in-memory — automático)

Os testes usam SQLite em memória configurado via `phpunit.xml`. Nenhuma configuração adicional é necessária:

```bash
docker-compose exec app composer install
```

```bash
docker-compose exec app php vendor/bin/phpunit
```

OBS: Você pode rodar os dois comandos anteriores sem "docker-compose exec app", caso queira executar fora do docker.

### Schema

```
users
  id, name, email (unique), password, timestamps

customers
  id, user_id (FK → users, cascade), name, email, phone, document, timestamps

addresses
  id, customer_id (FK → customers, cascade),
  cep (8), street, number, complement (nullable),
  neighborhood, city, state (2), country (default 'BR'), timestamps

revoked_tokens
  id, token_hash (SHA-256, unique), expires_at, created_at
```

---

## 🔐 Como autenticar

A API usa **JWT** (Bearer Token). O token é retornado no login e no registro.

### Registrar

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"João Silva","email":"joao@example.com","password":"senha123"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"joao@example.com","password":"senha123"}'
```

Resposta:
```json
{
  "success": true,
  "data": {
    "user": { "id": 1, "name": "João Silva", "email": "joao@example.com" },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

### Usar o token

Em todas as rotas protegidas, envie o header:

```
Authorization: Bearer <token>
```

### Logout (revoga o token)

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer <token>"
```

Após o logout, o token é adicionado a uma blacklist e rejeitado em requisições futuras, mesmo antes de expirar.

---

## 📬 Exemplos de requests

### Collection do Postman

Para testar a API de forma mais prática, importe a collection do Postman:

**[Baixar collection](https://drive.google.com/file/d/19xgLIFVy-sABH_kUjyUY7Fp8Zh7eWzQ-/view?usp=sharing)**

> ⚠️ **Atenção:** em requests que envolvem IDs na URL (ex: `/customers/{id}` ou `/customers/{id}/addresses/{addressId}`), certifique-se de que os IDs informados existem no banco. Rode o seeder (`php artisan db:seed`) para popular dados de exemplo.

Após importar, crie as seguintes variáveis de ambiente no Postman:

| Variável | Descrição | Exemplo |
|---|---|---|
| `CRM_WILLEY_BASE_URL` | URL base da API | `http://localhost:8000` |
| `CRM_WILLEY_JWT_TOKEN` | Token JWT obtido no login | `eyJ0eXAiOiJKV1Qi...` |

Ou importe diretamente o ambiente com as variáveis pré-configuradas:

**[Baixar environment](https://drive.google.com/file/d/1yTck0hbjTwqazgWrgLwH4j3GVz83F5ka/view?usp=sharing)**

---

### Customers

#### Listar clientes (com paginação)

```
GET /api/v1/customers?page=1&per_page=10
Authorization: Bearer <token>
```

Resposta:
```json
{
  "success": true,
  "message": "Clientes listados com sucesso.",
  "data": [
    { "id": 1, "name": "Maria Souza", "email": "maria@ex.com", "phone": "(11) 99999-1234" }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

#### Criar cliente

```bash
curl -X POST http://localhost:8000/api/v1/customers \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Maria Souza","email":"maria@example.com","phone":"(11) 99999-1234"}'
```

#### Buscar cliente

```
GET /api/v1/customers/1
Authorization: Bearer <token>
```

#### Atualizar cliente

```bash
curl -X PUT http://localhost:8000/api/v1/customers/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Maria Silva"}'
```

#### Deletar cliente

```bash
curl -X DELETE http://localhost:8000/api/v1/customers/1 \
  -H "Authorization: Bearer <token>"
```

---

### Addresses

#### Listar endereços de um cliente

```
GET /api/v1/customers/1/addresses
Authorization: Bearer <token>
```

#### Criar endereço (CEP é buscado automaticamente na BrasilAPI)

```bash
curl -X POST http://localhost:8000/api/v1/customers/1/addresses \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"cep":"01310100","number":"1000","complement":"Apto 42"}'
```

Resposta:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "cep": "01310100",
    "street": "Avenida Paulista",
    "number": "1000",
    "complement": "Apto 42",
    "neighborhood": "Bela Vista",
    "city": "São Paulo",
    "state": "SP",
    "country": "BR"
  }
}
```

#### Atualizar número sem reconsultar CEP

```bash
curl -X PUT http://localhost:8000/api/v1/customers/1/addresses/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"number":"2000"}'
```

#### Atualizar com novo CEP (reconsulta BrasilAPI)

```bash
curl -X PUT http://localhost:8000/api/v1/customers/1/addresses/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"cep":"04538133","number":"500"}'
```

#### Deletar endereço

```bash
curl -X DELETE http://localhost:8000/api/v1/customers/1/addresses/1 \
  -H "Authorization: Bearer <token>"
```

---

### Padrão de respostas

**Sucesso:**
```json
{ "success": true, "message": "...", "data": {} }
```

**Erro de validação (422):**
```json
{ "success": false, "message": "Dados inválidos.", "errors": { "campo": ["mensagem"] } }
```

**Não autorizado (401):**
```json
{ "success": false, "message": "Token não fornecido." }
```

**Não encontrado (404):**
```json
{ "success": false, "message": "Cliente não encontrado." }
```

---

## 🧪 Como rodar os testes

```bash
# todos os testes
php vendor/bin/phpunit

# somente feature tests
php vendor/bin/phpunit --filter Feature

# somente unit tests
php vendor/bin/phpunit --filter Unit

# um arquivo específico
php vendor/bin/phpunit tests/Feature/CustomerTest.php

# com cobertura (requer Xdebug ou PCOV)
php vendor/bin/phpunit --coverage-text
```

Os testes usam SQLite in-memory e não precisam do Docker.

### Estrutura de testes

```
tests/
├── Feature/                       # Testes de integração (HTTP end-to-end)
│   ├── AuthTest.php
│   ├── CustomerTest.php
│   ├── AddressTest.php
│   └── CepTest.php
└── Unit/
    ├── Application/Services/      # Testes unitários com Mockery
    │   └── AuthServiceTest.php
    ├── Infrastructure/
    │   └── BrasilApiCepGatewayTest.php
    └── Services/
        ├── CustomerServiceTest.php
        └── AddressServiceTest.php
```

---

## 📌 Decisões técnicas

### Por que Lumen e não Laravel?

Lumen é um micro-framework otimizado para APIs — sem overhead de templates, sessions e outros componentes que uma API REST não precisa. Boot mais rápido, footprint menor. Para o escopo deste projeto (API pura), é a escolha natural.

### Por que `firebase/php-jwt` e não `tymon/jwt-auth`?

O `tymon/jwt-auth` adiciona uma camada de abstração que complica a configuração em Lumen (guards customizados, publicação de configs, service providers extras). O `firebase/php-jwt` é a biblioteca de referência usada internamente por muitos pacotes e permite controle total do payload e validação com poucas linhas. Também facilita a implementação customizada como a blacklist de tokens revogados.

### Por que armazenar hash do token na blacklist e não o token inteiro?

O token JWT carrega informações do usuário e tem valor como credencial. Armazenar o token completo em banco seria um risco de segurança caso o banco fosse comprometido. O hash SHA-256 é irreversível — não é possível reconstruir o token a partir do hash — mas permite verificação eficiente em O(1) com índice único.

### Por que DDD e não MVC tradicional?

O MVC do Laravel/Lumen tende a inflar os Controllers e Models. A separação em Domain / Application / Infrastructure / Presentation garante que:
- **Regras de negócio** (quem pode ver o quê) ficam no Domain/Application, não no Controller
- **Repositórios** são contratos (interfaces), facilitando troca de implementação e mocks nos testes
- **Gateways externos** (BrasilAPI) são isolados — o serviço de endereço não conhece Guzzle

### Por que `CepGatewayInterface` está no Domain e não em Application?

O CEP é um dado que pertence ao domínio de endereços, mas sua obtenção é um detalhe de infraestrutura (chamada HTTP externa). Separar a interface (`CepGatewayInterface` no Domain) da implementação (`BrasilApiCepGateway` na Infrastructure) permite:
- Testar `AddressService` sem fazer chamadas HTTP reais (mock da interface)
- Trocar o provedor de CEP (ViaCEP, BrasilAPI, etc.) sem alterar nenhuma lógica de negócio

### Por que ownership retorna 404 e não 403?

Retornar 403 (Forbidden) confirmaria que o recurso existe mas o usuário não tem acesso. Retornar 404 não revela essa informação — para o usuário autenticado, um recurso de outro usuário simplesmente "não existe". Isso previne enumeração de IDs.

### Por que paginação é limitada a 100 itens por página?

`per_page` é validado com `max(1, min(100, $perPage))` para evitar que um client solicite `per_page=999999` e sobrecarregue o banco com uma única query não paginada.
