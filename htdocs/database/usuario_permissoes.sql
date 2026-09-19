-- Requisitos de ROLE
-- para o web app de Opções B3
--
-- considera estar logado como
-- superuser (postgres)
-- na db postgres
--
-- adicionar estes comentários posteriormente à documentação
-- e deletar este arquivo
--

CREATE DATABASE opcoes_b3;
CREATE ROLE opcoes_user WITH LOGIN PASSWORD 'quie/b7Aevi0UoXioKoo';
GRANT CONNECT ON DATABASE opcoes_b3 TO opcoes_user;
ALTER DATABASE opcoes_b3 OWNER TO opcoes_user;
