# Connect-V

**Connect-V** é um aplicativo de chat moderno, inspirado em plataformas como o WhatsApp, que permite comunicação em tempo real entre usuários individuais e em grupos. Com uma interface elegante, suporte a mensagens de texto, arquivos, mensagens de voz, reações e temas claro/escuro, o Connect-V oferece uma experiência de chat intuitiva e personalizável.

## Funcionalidades

- **Mensagens em Tempo Real**: Envie e receba mensagens de texto, imagens, arquivos e mensagens de voz.
- **Chats Individuais e em Grupo**: Converse com amigos ou crie grupos com múltiplos membros.
- **Reações a Mensagens**: Adicione emojis (👍, ❤️, 😊) às mensagens, visíveis para todos os participantes.
- **Indicadores de Mensagem Não Lida**: Mensagens não lidas são destacadas, com contagem na lista de chats.
- **Temas Claro e Escuro**: Alterne entre modos de interface para maior conforto visual.
- **Pesquisa de Usuários e Chats**: Encontre contatos ou conversas rapidamente.
- **Notificações de Status**: Veja quem está online ou quando um usuário foi visto pela última vez.
- **Interface Responsiva**: Funciona em dispositivos móveis e desktops.

## Tecnologias Utilizadas

- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **Backend**: PHP com PDO para acesso ao banco de dados
- **Banco de Dados**: MySQL (estrutura não incluída neste README; configure conforme `php/config.php`)
- **Bibliotecas Externas**:
  - Font Awesome para ícones
  - Tailwind CSS para estilização

## Pré-requisitos

- Servidor web (ex.: Apache) com PHP 7.4 ou superior
- MySQL ou outro banco de dados compatível com PDO
- Navegador moderno (Chrome, Firefox, Safari, etc.)

## Instalação

1. **Clone o Repositório**

   ```bash
   git clone https://github.com/lutondomartins/connect-v.git
   cd connect-v
   ```

2. **Configure o Banco de Dados**

   - Crie um banco de dados MySQL.
   - Configure as credenciais no arquivo `php/config.php`:

     ```php
     <?php
     $host = 'localhost';
     $db   = 'nome_do_banco';
     $user = 'seu_usuario';
     $pass = 'sua_senha';
     $charset = 'utf8mb4';
     
     $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
     $options = [
         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES   => false,
     ];
     
     try {
         $pdo = new PDO($dsn, $user, $pass, $options);
     } catch (PDOException $e) {
         throw new PDOException($e->getMessage(), (int)$e->getCode());
     }
     ?>
     ```
   - Importe a estrutura do banco de dados (crie tabelas como `users`, `chats`, `messages`, `message_reads`, `message_reactions`, `chat_members` conforme necessário).

3. **Configure o Servidor**

   - Coloque os arquivos em um servidor web com suporte a PHP.
   - Certifique-se de que a pasta `Uploads/` tenha permissões de escrita (ex.: `chmod 775 Uploads`).

4. **Acesse o Aplicativo**

   - Abra o navegador e acesse `http://seu-servidor/connect-v`.
   - Crie uma conta ou faça login para começar.

## Estrutura do Projeto

```
connect-v/
├── index.php          # Página principal do aplicativo
├── php/
│   ├── config.php     # Configuração do banco de dados
│   ├── get_messages.php # Recupera mensagens de um chat
│   ├── get_chats.php  # Lista os chats do usuário
│   ├── mark_messages_read.php # Marca mensagens como lidas
│   ├── react_to_message.php # Adiciona reações às mensagens
│   └── ...            # Outros arquivos PHP (não incluídos no README)
├── Uploads/           # Pasta para arquivos enviados (imagens, arquivos, etc.)
└── README.md          # Este arquivo
```

## Como Contribuir

1. Faça um fork do repositório.
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`).
3. Commit suas alterações (`git commit -m 'Adiciona nova funcionalidade'`).
4. Envie para o repositório remoto (`git push origin feature/nova-funcionalidade`).
5. Abra um Pull Request descrevendo suas mudanças.

## Problemas Conhecidos

- O sistema usa polling para atualizações (a cada 5-10 segundos), o que pode ser otimizado com WebSockets.
- A estrutura do banco de dados não está incluída; precisa ser criada manualmente.
- Algumas funcionalidades (ex.: seletor de emojis) ainda não estão implementadas.

## Licença

Este projeto está licenciado sob a MIT License. Veja o arquivo `LICENSE` para mais detalhes.

## Contato

Para dúvidas ou sugestões, entre em contato via seu-email@exemplo.com ou abra uma issue no GitHub.