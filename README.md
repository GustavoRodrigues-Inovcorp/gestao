# Gestão - Sistema de Gestão Empresarial

Sistema completo de gestão empresarial desenvolvido com **Laravel 11**, **Vue 3** (Inertia.js) e **Tailwind CSS**.

## Funcionalidades

- **Contactos**: Gestão completa de contactos com entidades associadas
- **Entidades**: Registos de empresas/organizações com pesquisa VIES integrada
- **Artigos**: Catálogo de produtos/serviços
- **Propostas**: Geração de propostas comerciais com PDF
- **Encomendas**: Gestão de encomendas de clientes e fornecedores
- **Ordens de Trabalho**: Controlo de projetos e tarefas
- **Calendário**: Agenda de eventos e ações
- **Arquivos**: Gestão de ficheiros digitais associados
- **Módulo Financeiro**: Gestão de faturas, contas correntes e pagamentos
- **Activity Log**: Registo automático de todas as atividades do sistema

## Tecnologias

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS
- **Base de Dados**: MySQL/MariaDB
- **Build**: Vite, npm
- **Autenticação**: Laravel Fortify
- **Autorização**: Spatie Permissions

## Instalação

### Pré-requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- npm ou yarn
- MySQL/MariaDB

### Passos

```bash
# Clonar repositório
git clone https://github.com/GustavoRodrigues-Inovcorp/gestao.git
cd gestao

# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install

# Copiar ficheiro de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Configurar base de dados no .env
# DATABASE_URL=mysql://user:password@localhost:3306/gestao

# Executar migrações
php artisan migrate

# Compilar assets
npm run build
```

## Desenvolvimento

```bash
# Iniciar servidor de desenvolvimento (2 terminais)
php artisan serve

# Terminal 2: Watch assets
npm run dev
```

Aceder em: `http://localhost:8000`

## Estrutura do Projeto

```
├── app/
│   ├── Actions/          # Ações (Fortify)
│   ├── Controllers/      # Controladores
│   ├── Helpers/          # Funções auxiliares
│   ├── Listeners/        # Event Listeners
│   ├── Mail/             # Classes de email
│   ├── Models/           # Modelos Eloquent
│   ├── Providers/        # Service Providers
│   └── Services/         # Serviços
├── resources/
│   ├── js/               # Componentes Vue
│   ├── views/            # Templates Blade
│   └── css/              # Estilos Tailwind
├── routes/               # Rotas (web, api, auth)
├── database/
│   ├── migrations/       # Migrações
│   └── seeders/          # Seeders
├── tests/                # Testes unitários/feature
└── storage/              # Ficheiros gerados
```

## Scripts Úteis

```bash
# Testes
php artisan test

# Migrações
php artisan migrate              # Executar
php artisan migrate:rollback     # Reverter última
php artisan migrate:refresh      # Resetar todas

# Cache
php artisan cache:clear
php artisan config:cache

# Build production
npm run build
php artisan optimize
```

## Permissões e Roles

O sistema utiliza **Spatie Permissions** para controlo de acesso baseado em roles e permissões.

## Logs

Logs de atividades salvos em:
- `storage/logs/` — logs da aplicação
- ActivityLog — tabela de atividades registadas

## Suporte

Para questões ou problemas, consulte a documentação de [Laravel](https://laravel.com/docs) e [Inertia.js](https://inertiajs.com).

## Licença

MIT License © 2026
