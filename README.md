# Trabalho PHP - Sistema de Controle de Opções e Posições B3

O sistema é uma aplicação web desenvolvida para a disciplina de Linguagens de Programação Web. O seu objetivo é permitir a autenticação de utilizadores, gestão de contratos de opções B3 (CALL e PUT), controle de posições financeiras ativas e encerradas, histórico de transações e visualização do resumo financeiro no dashboard.

## Integrantes e Atividades

- **Ian Carlos (iancfa):** 
#### Trabalho 1

Implementação completa do módulo de Contratos/Opções (`ContractController`), abrangendo operações de criação, edição e listagem. Responsável pelo suporte a rotas bilíngues (PT/EN), integração do token CSRF nos formulários de contratos, persistência de dados antigos no formulário em caso de erro de validação (`old input`) e gestão de mensagens temporárias de feedback (`Session::flash`).

#### Trabalho 2

Responsável pela garantia de qualidade (QA), testes de ambiente e documentação técnica. Realizou a auditoria de código, corrigindo erros pontuais.

- **Geraldo Nascimento (geraldog):** 
#### Trabalho 1

Desenvolvimento da infraestrutura, arquitetura de dados e integração financeira avançada. Implementou a biblioteca `php-finance`, a calculadora do modelo Black-Scholes, a renderização de gráficos, a paginação global do sistema e refatorou os controladores, rotas e a gestão do CSRF em diversos formulários.

#### Trabalho 2

Desenvolvimento da infraestrutura, arquitetura de dados e integração financeira avançada. Implementou a biblioteca `php-finance`, a calculadora do modelo Black-Scholes, a renderização de gráficos, a paginação global do sistema e refatorou os controladores, rotas e a gestão do CSRF em diversos formulários.

- **Mateus Hahn (mateushahndev):**  
#### Trabalho 1

Concepção e construção da interface gráfica (UI) e experiência visual do sistema. Criação da folha de estilos CSS (`styles.css`), do template de layout principal (`layout.php`), da página de erro 404 personalizada e de todas as views HTML/PHP (telas de login, registo, formulários e tabelas de exibição de dados).

#### Trabalho 2

Concepção estrutural e construção da interface gráfica (UI/UX). Configurou o ambiente base com Composer (autoload PSR-4), desenvolveu os templates de layout, o menu de navegação, a estilização CSS global e implementou a primeira versão analítica da calculadora de opções e do `AnalysisController`.

## Particularidades do Sistema

- **Arquitetura MVC Própria (PHP 8.4+):** Construído com tipagem estrita (`declare(strict_types=1);`), utilizando padrão Front Controller, autoloader simples e persistência no PostgreSQL via PDO (prepared statements).
- **Gestão de Dependências:** Utilização do `Composer` para integração de bibliotecas de terceiros voltadas para cálculos financeiros avançados (ex: `academe/php-finance`).
- **Mecanismo de Segurança CSRF:** Proteção ativa contra Cross-Site Request Forgery em todas as submissões POST, injetada nos formulários pela função `\App\Core\csrf_field()`.
- **Controle de Acesso e Autenticação:** Rotas protegidas através do método `requireAuth()`, garantindo que apenas utilizadores com sessão ativa e regenerada acessem áreas sensíveis.

## Instalação e Configuração

### Pré-requisitos
- PHP 8.4+ (com tipagem estrita e servidor embutido)
- PostgreSQL 14+
- Composer (Gestor de pacotes PHP)
- Git

### Passo a Passo

1. **Clonar o repositório e instalar dependências:**
```bash
git clone [https://github.com/mateushahndev/trabalho-php.git](https://github.com/mateushahndev/trabalho-php.git)
cd trabalho-php
composer install
```

2. **Configuração do Banco de Dados no PostgreSQL:**
   Crie o banco de dados e o utilizador de conexão (role) utilizando o terminal ou o pgAdmin:
```sql
CREATE DATABASE opcoes_b3;
CREATE USER opcoes_user WITH PASSWORD 'quie/b7Aevi0UoXioKoo';
GRANT CONNECT ON DATABASE opcoes_b3 TO opcoes_user;
ALTER DATABASE opcoes_b3 OWNER TO opcoes_user;
```

3. **Importação do Esquema e Dados Iniciais:**
   Conectado à base de dados `opcoes_b3`, execute a importação dos ficheiros SQL localizados na pasta `database/`:
```bash
psql -U opcoes_user -d opcoes_b3 -f database/schema.sql
psql -U opcoes_user -d opcoes_b3 -f database/seed.sql
```

4. **Correção de Permissões de Acesso (Crucial):**
   Caso as tabelas tenham sido criadas por um superutilizador (como `postgres`), o utilizador da aplicação enfrentará o erro de privilégios insuficientes (`SQLSTATE[42501]`). Conceda o acesso executando:
```sql
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO opcoes_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO opcoes_user;
```

5. **Iniciar o Servidor da Aplicação:**
   Na raiz do projeto (onde se encontra a pasta `public/`), inicie o servidor embutido do PHP:
```bash
php -S localhost:8000 -t public/
```

6. **Aceder no Navegador:**
   Abra `http://localhost:8000` para aceder ao sistema e criar uma nova conta de utilizador.
```
