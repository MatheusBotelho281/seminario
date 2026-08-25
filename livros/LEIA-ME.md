# Sistema de Upload para FTP — Como instalar

Este sistema é feito em PHP puro (não precisa instalar nada no computador,
só precisa de uma hospedagem que já tenha PHP — praticamente todas têm).

## O que tem na pasta

- `index.html` → a tela que o usuário vê
- `style.css` e `script.js` → visual e funcionamento da tela
- `upload.php` → recebe o arquivo e envia para o FTP
- `list.php` → lista os arquivos que já estão no FTP
- `config.php` → **onde você coloca os dados do seu FTP**
- `ftp_helper.php` → funções internas (não precisa mexer)

## Passo a passo

### 1. Edite o `config.php`
Abra o arquivo `config.php` em qualquer editor de texto e preencha:

```php
'host' => 'ftp.seusite.com.br',   // endereço do FTP
'user' => 'usuario_ftp',          // usuário
'pass' => 'senha_ftp',            // senha
```

Se você não sabe esses dados, são os mesmos que você usaria para entrar
no FTP pelo FileZilla ou por um programa parecido. Normalmente sua
hospedagem (Hostgator, Locaweb, KingHost, etc.) te fornece isso no
painel de controle, em algo como "Contas FTP".

### 2. Envie a pasta inteira para o seu site
Envie todos esses arquivos (por FTP mesmo, ou pelo Gerenciador de
Arquivos do cPanel) para dentro da pasta do seu site, por exemplo em:

```
public_html/upload/
```

### 3. Acesse pelo navegador
Abra no navegador o endereço correspondente, por exemplo:

```
https://seusite.com.br/upload/
```

Pronto — vai aparecer a tela de envio de arquivos, com a área para
arrastar o arquivo e, logo abaixo, a lista dos arquivos que já estão
no FTP.

## Observações importantes

- **A pasta `remote_dir`** no `config.php` (padrão `/uploads`) é a pasta
  DENTRO do FTP onde os arquivos serão guardados e listados. Se preferir
  que fique na raiz do FTP, mude para `/`.
- **Tamanho máximo de upload**: além do limite definido em `config.php`,
  o próprio servidor (PHP) tem um limite padrão (geralmente 8 MB ou
  2 MB). Se precisar enviar arquivos maiores, peça ao suporte da
  hospedagem para aumentar `upload_max_filesize` e `post_max_size` no
  PHP, ou crie um arquivo `.htaccess`/`php.ini` conforme a hospedagem
  orientar.
- **Segurança**: como qualquer pessoa que acessar essa página poderá
  enviar arquivos, se isso for um problema, me avise que posso
  adicionar uma senha simples de acesso à tela.
- Se aparecer erro de conexão, confira: endereço do host (sem
  "ftp://"), usuário, senha e se o modo passivo (`passive => true`)
  está ativado — isso resolve a maioria dos problemas de firewall.

## Testando localmente antes de subir (opcional)

Se quiser testar no seu computador antes de mandar para a hospedagem,
você pode instalar o PHP e rodar, na pasta do projeto:

```
php -S localhost:8000
```

E abrir `http://localhost:8000` no navegador. A conexão FTP feita pelo
`upload.php`/`list.php` continuará indo para o servidor FTP remoto
configurado em `config.php` normalmente.
