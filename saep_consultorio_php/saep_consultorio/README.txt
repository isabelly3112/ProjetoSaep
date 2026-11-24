SISTEMA DE GESTÃO PARA CONSULTÓRIO MÉDICO - SAEP
Tecnologias: PHP 8+, MySQL 8+, PDO, HTML e CSS.

ARQUIVOS
- login.php: Tela 1 - Login
- index.php: Tela 2 - Tela inicial
- acompanhantes.php: Tela 3 - Gestão de acompanhantes
- pacientes.php: Tela 4 - Gestão de pacientes
- agendamentos.php: Tela 5 - Gestão de agendamentos
- logout.php: encerra a sessão
- config.php: conexão com o banco e configuração da sessão
- includes/: autenticação, funções e estrutura comum
- assets/style.css: estilos

INSTALAÇÃO NO XAMPP
1. Copie a pasta saep_consultorio para C:\xampp\htdocs\
2. Abra o phpMyAdmin e execute o script SQL fornecido para criar e preencher saep_db.
3. Gere a chave no terminal:
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   Guarde a chave em local seguro, fora do banco e do código-fonte.
4. Abra o arquivo C:\xampp\apache\conf\httpd.conf e, no final, adicione as linhas abaixo (substitua COLE_A_CHAVE_GERADA pela chave de 64 caracteres):
   SetEnv APP_DB_HOST "127.0.0.1"
   SetEnv APP_DB_NAME "saep_db"
   SetEnv APP_DB_USER "root"
   SetEnv APP_DB_PASS ""
   SetEnv CPF_ENCRYPTION_KEY "COLE_A_CHAVE_GERADA"
5. Salve o arquivo e reinicie o Apache pelo painel do XAMPP.
6. Acesse http://localhost/saep_consultorio/login.php

USUÁRIOS DE TESTE CONFORME O SQL ENVIADO
admin / admin123
recepcao / recepcao123
drsilva / medico123

OBSERVAÇÕES IMPORTANTES
- O sistema usa os nomes de tabelas e colunas do SQL enviado.
- As sessões expiram após 15 minutos sem atividade.
- O CPF informado em novos cadastros é protegido com AES-256-GCM e nunca é exibido completo.
- A chave de criptografia é obrigatória por variável de ambiente. Não a coloque no banco, no SQL ou neste projeto.
- Para editar um cadastro, informe novamente o CPF, pois o formulário não recupera nem exibe o CPF original.
- Os CPFs fictícios já inseridos no SQL são mostrados mascarados. Para descriptografar os registros de demonstração, seria necessária a chave exata usada na geração desses valores; ela não foi fornecida no SQL.
- O projeto usa consultas preparadas, escape de saída e token CSRF nos formulários.
