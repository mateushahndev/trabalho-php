# Trabalho PHP - Sistema de Controle de Opções e Posições B3

O sistema é uma aplicação web desenvolvida para a disciplina de Linguagens de Programação Web. Seu objetivo é permitir a autenticação de utilizadores, 
gestão de contratos de opções B3 (CALL e PUT), controle de posições financeiras ativas e encerradas, histórico de transações e visualização de resumo 
financeiro no dashboard.

## Integrantes e Atividades

- Ian Carlos (iancfa): Implementação completa do módulo de Contratos/Opções (`ContractController`), abrangendo operações de criação, edição e listagem.
  Responsável pelo suporte a rotas bilíngues (PT/EN), integração do token CSRF nos formulários de contratos, persistência de dados antigos no formulário
  em caso de erro de validação (`old input`) e gestão de mensagens temporárias de feedback (`Session::flash`).

- Geraldo Nascimento (geraldog): Desenvolvimento do Core e arquitetura da aplicação (classe abstrata `Controller`, `Router`, suporte a CSRF, gestão de
  `Session`, `bootstrap.php` e autoloader). Criação do esquema relacional no PostgreSQL (`database/schema.sql`) e de todas as Models (`Model`, `User`,
  `Contract`, `Position`, `Transaction`). Implementação da lógica de negócio nos controladores de Autenticação, Perfil, Transações, Dashboard e Posições
  (incluindo o fluxo de fechar posição).

- Mateus Hahn (mateushahndev): Concepção e construção da interface gráfica (UI) e experiência visual do sistema. Criação da folha de estilos CSS (`styles.css`),
  do template de layout principal (`layout.php`), da página de erro 404 personalizada e de todas as views HTML/PHP (telas de login, registo, formulários e tabelas
  de exibição de dados).

## Particularidades do Sistema

- Arquitetura MVC Própria: Construído em PHP 8.1+ sem a utilização de frameworks externos, utilizando autoloader simples, padrão Front Controller e comunicação com banco PostgreSQL via PDO com instruções preparadas (prepared statements).
- Mecanismo de Segurança CSRF: Proteção ativa contra Cross-Site Request Forgery em todas as submissões POST, verificada via middleware/core e injetada nos formulários pela função `\App\Core\csrf_field()`.
- Controle de Acesso e Autenticação: Proteção de rotas através do método `requireAuth()`, garantindo que apenas utilizadores autenticados acessem áreas restritas como dashboard, posições, transações e contratos.
- Tratamento do Fuso Horário: Aplicação configurada explicitamente para o fuso horário `America/Sao_Paulo`, com tratamento de datas do PostgreSQL para exibição correta de vencimentos e registros operacionais.

## Instalação e Configuração

### Pré-requisitos
- PHP 8.1+ (com extensões pdo e pdo_pgsql)
- PostgreSQL 14+
- Git

### Passo a Passo

1. Clonar o repositório:
git clone git@github.com:mateushahndev/trabalho-php.git
cd trabalho-php

2. Iniciar o PostgreSQL e importar o banco de dados:
sudo systemctl start postgresql
psql -U postgres -d nome_da_database -f database/schema.sql

3. Iniciar o servidor do PHP:
php -S localhost:8080 -t public

4. Acessar no navegador:
http://localhost:8080
