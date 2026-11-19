# KultDash – Central de Operações Agrícolas

Sistema web em PHP + MySQL + JavaScript (tela única com modais), gráficos com Google Charts e relatórios PDF com HTML2PDF.

## Estrutura

```
kultdash/
├── index.php, login.php, dashboard.php, logout.php
├── conexao.php          → configuração do MySQL (host, usuário, senha, porta, limite de arquivo)
├── composer.json        → spipu/html2pdf 5.2.8
├── css/style.css        → dashboard (cores e tamanhos em variáveis :root)
├── css/login.css        → tela de login
├── js/script.js         → modais, CRUD via fetch, resumo, linhas de conexão
├── js/login.js          → abertura animada do login
├── js/linhas.js         → linhas de circuito entre os cartões e a propriedade
├── js/sons.js           → sons ao passar o mouse (nomes e volume no início do arquivo)
├── js/olho.js           → olho do logo (olha de um lado para o outro e pisca raramente) e teclado do quadro da propriedade
├── js/graficos.js       → Google Charts
├── img/                 → logo_kultdash.png (abertura e ícone), olho_contorno.png + olho_iris.png (logo do cabeçalho em 2 camadas) e logo_vision.png (login)
├── sons/                → coloque aqui os MP3 dos sons (veja sons/LEIAME.txt)
├── includes/            → auth.php, api.php, funcoes.php, categorias.php (tipos de cada categoria) e cenas.php (ilustrações animadas)
├── modulos/             → propriedades, areas, animais, maquinas, prontuarios, arquivos (api.php = endpoints JSON) e opcoes.php
├── graficos/dados_graficos.php
├── relatorios/          → pdf.php + conteudo_*.php (4 relatórios) + _cabecalho.php
└── banco/
    ├── db_kultive.sql            → cria o banco e as tabelas
    ├── dados_iniciais.sql        → tipos + usuário admin + MEDIUMBLOB (pode rodar de novo sem duplicar)
    ├── dados_exemplo.sql         → propriedades, animais, máquinas, áreas e ocorrências de teste (opcional)
    └── limpar_dados_exemplo.sql  → remove os dados de exemplo
```

Requisitos: PHP 7.4 ou superior com as extensões `mysqli`, `mbstring`, `fileinfo` e `gd`; MySQL 5.7+/8.x ou MariaDB; Composer.

---

## A) Com XAMPP e phpMyAdmin

1. Instale o XAMPP e copie a pasta `kultdash` para `C:\xampp\htdocs\`.
2. Em `C:\xampp\php\php.ini`, remova o `;` de `;extension=gd` (as demais extensões já vêm ativas) e reinicie o Apache.
3. Inicie Apache e MySQL no XAMPP Control Panel.
4. No phpMyAdmin (http://localhost/phpmyadmin), aba *Importar*, importe nesta ordem: `banco/db_kultive.sql`, `banco/dados_iniciais.sql` e, se quiser dados de teste, `banco/dados_exemplo.sql`.
5. No terminal, dentro da pasta do projeto: `composer require spipu/html2pdf:5.2.8` (cria a pasta `vendor/`).
6. Acesse http://localhost/kultdash/

O `conexao.php` já vem com o padrão do XAMPP (`127.0.0.1`, `root`, senha vazia, porta 3306).

---

## B) Com MySQL Workbench e NetBeans

Nesta configuração o banco é um MySQL Server separado (o do Workbench) e o NetBeans edita e executa o PHP. É preciso ter um servidor PHP: o mais simples é instalar o XAMPP e usar **somente o Apache** (não inicie o MySQL do XAMPP, para não disputar a porta 3306 com o MySQL Server).

### 1. Banco de dados (Workbench)

1. Abra a conexão com o seu servidor (normalmente `127.0.0.1`, porta `3306`, usuário `root`).
2. Menu *File → Run SQL Script…* e escolha, nesta ordem, executando cada um **inteiro**:
   1. `banco/db_kultive.sql`
   2. `banco/dados_iniciais.sql`
   3. `banco/dados_exemplo.sql` (opcional)
3. Os scripts já desligam o modo seguro do Workbench (`SET SQL_SAFE_UPDATES`) quando precisam. Atualize o painel *Schemas* para ver o `db_kultive`.
4. Para apagar os dados de teste depois: execute `banco/limpar_dados_exemplo.sql`.

Dados de acesso criados: usuário `admin`, senha `password`.

### 2. Configurar a conexão

Edite `conexao.php` com os dados da **sua** conexão do Workbench:

```php
const DB_HOST = '127.0.0.1';
const DB_USUARIO = 'root';
const DB_SENHA = 'a_senha_do_seu_mysql';
const DB_NOME = 'db_kultive';
const DB_PORTA = 3306;
```

Use `127.0.0.1` em vez de `localhost` (evita problemas de resolução no Windows). Se a porta do seu servidor não for 3306, ajuste `DB_PORTA`.

### 3. PHP e Composer

1. Em `php.ini` (no XAMPP: `C:\xampp\php\php.ini`), confirme que estão ativas (sem `;`): `extension=mysqli`, `extension=mbstring`, `extension=fileinfo` e `extension=gd`. Reinicie o Apache depois.
2. Instale o Composer (https://getcomposer.org) e, na pasta do projeto, rode: `composer require spipu/html2pdf:5.2.8`. Se o Composer reclamar de `ext-gd`, ative a extensão (passo 1) ou use `--ignore-platform-req=ext-gd`.

### 4. NetBeans

Os menus podem variar um pouco conforme a versão do NetBeans (versões mais novas precisam do suporte a PHP instalado como plugin).

1. Coloque a pasta do projeto em `C:\xampp\htdocs\kultdash` (assim o Apache já a serve).
2. *File → New Project → PHP → PHP Application with Existing Sources*, e selecione essa pasta.
3. Na etapa *Run Configuration*, deixe *Run As* em **Local Web Site (running on local web server)** e confira se o *Project URL* é `http://localhost/kultdash/`.
4. Abra o `dashboard.php` e execute o projeto (botão *Run*, tecla F6). O navegador abre o sistema.

Se o projeto ficar em outra pasta (fora do `htdocs`), marque *Copy files from Sources Folder to another location* e aponte para `C:\xampp\htdocs\kultdash`, ou use a alternativa sem Apache: abra um terminal na pasta do projeto, rode `php -S localhost:8000` e acesse http://localhost:8000/dashboard.php (exige o PHP instalado e no PATH).

### 5. Limites de envio de arquivos

O sistema aceita arquivos de até 10 MB (`TAMANHO_MAX_ARQUIVO` em `conexao.php` e em `js/script.js`, que devem ser iguais). Para isso funcionar:

- **MySQL**: `max_allowed_packet` de pelo menos 16 MB. No Workbench: *Administration → Options File → Networking*, ou, de forma temporária, `SET GLOBAL max_allowed_packet = 33554432;` (reconecte depois). Para deixar permanente, edite o `my.ini` (no Windows costuma ficar em `C:\ProgramData\MySQL\MySQL Server 8.0\`) e reinicie o serviço.
- **PHP** (`php.ini`): `upload_max_filesize=20M` e `post_max_size=25M`, depois reinicie o Apache.

Se algum limite estiver baixo, o próprio sistema informa qual deve ser aumentado.

---

## Login

A tela `login.php` (marca VisionDash, com abertura animada) autentica pelo **e-mail** e pela senha da tabela `usuario`, usando `password_verify()`. Usuário de teste criado por `dados_iniciais.sql`: e-mail `admin@kultdash.local`, senha `password`. Para pular a animação de abertura, pressione qualquer tecla.

- Sem sessão, o dashboard, os relatórios e os endpoints redirecionam para o login (ou respondem "Sessão expirada").
- O botão "Sair" do dashboard encerra a sessão.
- Para desenvolver sem login, mude `LOGIN_ATIVO` para `false` em `includes/auth.php` (entra automático como `admin`).
- As senhas precisam estar gravadas como hash (60 caracteres, começando com `$2y$`); senha em texto puro não é aceita. Para gerar: `php -r "echo password_hash('senha', PASSWORD_DEFAULT);"`. Usuário novo: grave esse hash na coluna `senha` e vincule o usuário a uma propriedade em `usuario_propriedade`.
- Depois do login, o dashboard abre com uma animação (logo girando e nome KultDash em tela cheia), só uma vez por acesso: `css/abertura.css`, `js/abertura.js` e `img/logo_kultdash.png`. Qualquer tecla pula.
- Se o login recusar credenciais corretas, abra `http://localhost/kultdash/diagnostico_login.php` (só funciona em localhost): ele mostra o que há na tabela `usuario` e se a senha confere. **Apague esse arquivo depois de usar.**
- Logo da tela de login: `img/logo_vision.png`. Cores e animações: `css/login.css`; sequência de inicialização (textos e tempos): `js/login.js`.

## Pontos de atenção

- **Tipos por categoria**: o agrupamento fica em `includes/categorias.php` (lista de nomes de tipo por categoria: `animal`, `maquina`, `area`, `arquivo`, `usuario`). Para um tipo novo aparecer na lista certa, crie-o na tabela `tipagem` e acrescente o nome em `categorias.php`. Ocorrências aceitam qualquer tipo (exceto os de usuário). Se o banco ainda tiver a coluna `categoria` de versões anteriores, ela é ignorada; pode removê-la com `ALTER TABLE tipagem DROP COLUMN categoria;`.
- **Unidade**: tamanhos em hectares (`ha`), definido em `UNIDADE_TAMANHO` (`js/script.js`).
- **Exclusões**: registros com vínculos (ex.: animal citado em uma ocorrência) não são excluídos, e o sistema avisa. Excluir uma ocorrência remove também seus arquivos.
- **Aparência do dashboard**: visual minimalista em verde, com um efeito de TV/transmissão antiga muito discreto (linhas de varredura, granulação e uma faixa lenta) no início de `css/style.css` (cores nas variáveis `:root`; para desligar o efeito, apague os blocos `body::before`, `body::after` e `html::after`). As ilustrações animadas (estilo gravura) dos cartões e da propriedade ficam em `includes/cenas.php` (as cenas `centro`, `areas` e `animais` foram redesenhadas com celeiro, silo, campos em perspectiva, plantas e vacas; nuvens, plantas, cata-vento, cauda e orelha da vaca se movem sozinhos). O quadro central da propriedade é o botão que abre o modal de propriedades (indicado por "GERENCIAR ›"). O logo do cabeçalho usa `img/olho_contorno.png` (fixo) e `img/olho_iris.png` (se move, controlado por `js/olho.js`); a abertura continua usando `img/logo_kultdash.png`.
- **Sons**: coloque os MP3 em `sons/` (nomes em `sons/LEIAME.txt`). Tocam ao passar o mouse nos cartões e no quadro da propriedade; o botão de alto-falante no cabeçalho liga e desliga.
- **Gráficos** precisam de internet (carregam o Google Charts).
- **CSS/JS não atualizou?** Pressione Ctrl+F5.

## Solução de problemas

| Sintoma | Causa provável |
|---|---|
| "Sem conexão com o banco de dados" no rodapé | MySQL desligado, ou host/usuário/senha/porta errados em `conexao.php` |
| Erro ao importar `dados_iniciais.sql` no Workbench (código 1175) | Modo seguro ativo: rode o script inteiro, ele já desliga o modo seguro |
| "Class Spipu\Html2Pdf not found" / "HTML2PDF não instalado" | Falta rodar o `composer require` (pasta `vendor/` ausente) |
| Lista de tipos vazia | Importe `banco/dados_iniciais.sql` e confira se o nome do tipo está em `includes/categorias.php` |
| Envio de arquivo falha com aviso de limite | Ajuste `max_allowed_packet` (MySQL) e `upload_max_filesize` / `post_max_size` (PHP) |
| Gráficos em branco | Sem internet, ou propriedade sem dados |
