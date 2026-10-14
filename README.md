# Finder

O Finder é uma aplicação Laravel para criar e gerir websites, páginas e lojas online através de uma interface Livewire.

## Requisitos

- PHP 8.3 ou superior
- Composer
- Node.js compatível com as versões indicadas em `package.json`
- Uma base de dados suportada pela configuração Laravel (SQLite para desenvolvimento local, se configurado)

## Instalação local

Na pasta do projeto:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Configura a base de dados no ficheiro `.env`. Para SQLite, confirma que o caminho da base de dados existe e corresponde à configuração local. Depois executa:

```powershell
php artisan migrate
npm install
npm run build
```

Se já existir um ficheiro `.env`, não o substituas: mantém as tuas credenciais e configurações existentes.

## Desenvolvimento

Para executar o servidor Laravel:

```powershell
php artisan serve
```

Noutro terminal, para compilar assets durante o desenvolvimento:

```powershell
npm run dev
```

Se utilizares Laravel Herd, abre o domínio local configurado para o projeto, por exemplo `http://finder.test`.

## Verificações

```powershell
composer lint:check
composer types:check
php artisan test
npm run build
```

A verificação `composer lint:check` deve passar antes de considerar a suite de testes validada. Não ignores erros de formatação ou análise estática para forçar uma aprovação.

## Áreas principais

- Dashboard e gestão de websites
- Editor de páginas e secções
- Menus e biblioteca multimédia
- Catálogo, carrinho e checkout
- Encomendas, pagamentos e stock
- Definições e gestão de utilizadores

## Segurança e publicação

- Não publiques o ficheiro `.env`, chaves, tokens ou credenciais.
- Em produção, define `APP_ENV=production` e `APP_DEBUG=false`.
- Configura HTTPS, sessões, filas, correio eletrónico, armazenamento e backups conforme o ambiente.
- Executa auditorias de dependências PHP e JavaScript antes de cada publicação.
- Testa login, permissões entre websites, publicação, checkout e stock num ambiente de teste antes de atualizar produção.

## Estado do projeto

Este documento descreve o ponto de partida; os comandos e testes devem ser confirmados no ambiente onde o Finder é executado.
