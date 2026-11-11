# SmartLocker — Painel administrativo (novembro/2026)

O sistema agora separa **usuário comum** e **administrador**.
Um único formulário de login decide para onde cada um vai, consultando o banco:

| Conta | Depois do login | Pode |
|---|---|---|
| `usuario` | `sistema/dashboard.php` | Reservar, ver a própria reserva, QR Code e histórico (site e app) |
| `admin` | `sistema/admin/` | Ver os números do sistema, gerenciar usuários e armários (só pelo site) |

---

## 1. Colocar no ar (passo a passo)

### 1.1 Rodar a migração (uma vez em cada banco)

Abra o **MySQL Workbench** (ou phpMyAdmin → aba SQL) no banco `devsphar_smartlocker` e execute
`sistema/sql/migracao_2026_11_admin.sql`.

- Rode no banco de teste do grupo **e** no da HostGator.
- Ela precisa da `migracao_2026_11.sql` já aplicada (a de novembro, da API).
- Não apaga nada: só corrige perfis vazios para `usuario` e cria as colunas `bloqueado` e `bloqueado_em`.
- Rode **uma vez só**. Se rodar de novo, o MySQL avisa "Duplicate column name 'bloqueado'"; isso só quer dizer que já foi aplicada.

> Enquanto a migração não for rodada, o site mostra "O banco de dados está desatualizado (falta: bloqueado)".

### 1.2 Criar o primeiro administrador

1. Cadastre a conta normalmente pela tela de cadastro do site (use uma senha forte).
2. No Workbench, rode trocando o e-mail:

```sql
UPDATE usuarios SET perfil = 'admin' WHERE email = 'admin@exemplo.com';
```

3. Saia e entre de novo no site: você cai direto no painel administrativo.

Outros comandos úteis:

```sql
-- quem é administrador
SELECT id, nome, email FROM usuarios WHERE perfil = 'admin';

-- tirar o acesso de administrador
UPDATE usuarios SET perfil = 'usuario' WHERE email = 'admin@exemplo.com';
```

Não existe senha de administrador fixa: o admin entra com o e-mail e a senha da própria conta,
guardada com `password_hash()` como as outras.

### 1.3 Arquivo de configuração

O `sistema/config/config.exemplo.php` agora está no GitHub (antes faltava). Copie para
`config.local.php` e preencha com os dados do banco de cada lugar. O `config.local.php` continua fora do Git.

---

## 2. O que o painel faz

**Acesso:** `https://devsphare.com.br/sistema/admin/` (ou só faça login com uma conta admin).

| Seção | Funções |
|---|---|
| **Dashboard** | Usuários (comuns, admins, bloqueados), armários (total, disponíveis, ocupados, manutenção), reservas (total, ativas, concluídas, canceladas), acessos liberados hoje, últimas 8 reservas. Tudo direto do banco, atualizado a cada 30 s. |
| **Usuários** | Busca por nome, e-mail ou CPF; filtro todos / usuários / admins / bloqueados; editar nome, e-mail, CPF, telefone e **chaveiro RFID (UID)**; bloquear e desbloquear; excluir conta sem histórico. |
| **Armários** | Cadastrar, editar número e localização, colocar em manutenção e liberar, ver quem está usando, remover armário sem histórico. |

### Regras

- **Bloquear** uma conta: cancela as reservas ativas (o armário fica livre), derruba o login do app,
  derruba a sessão do site na próxima página e faz o chaveiro RFID e o QR Code pararem de abrir.
- O admin **não** pode bloquear nem excluir a própria conta, nem outro admin (tire o perfil no banco antes).
- **Excluir** só vale para contas e armários que nunca tiveram reserva nem acesso. Os outros devem ser bloqueados
  (usuário) ou postos em manutenção (armário), para preservar o histórico.
- Um armário com reserva ativa não entra em manutenção.
- "Ocupado" não é escolhido à mão: o sistema calcula pelas reservas.
- O **número** do armário é o que o ESP32 envia em `validar_acesso.php`; precisa bater com a placa da porta.
- O perfil (usuario/admin) **não aparece em nenhum formulário**. Se alguém enviar `perfil` ou `role`
  no cadastro, na edição ou pelo app, o campo é ignorado.

---

## 3. Segurança implementada

| Proteção | Onde |
|---|---|
| Perfil e bloqueio lidos **do banco a cada requisição** (nunca da sessão, cookie ou navegador) | `config/acesso.php`, `api/_base.php` |
| Página admin: sem login → login; usuário comum → **HTTP 403** sem nada do painel | `config/acesso.php` → `exigirAdminPagina()` |
| API admin: só admin logado **pelo site**; token do app recebe 403 | `api/_base.php` → `exigirAdmin()` |
| **CSRF**: toda requisição que altera dados pelo site precisa do cabeçalho `X-CSRF-Token` (o app usa token e não é afetado) | `config/sessao.php`, `api/_base.php` |
| Cadastro grava `perfil = 'usuario'` explicitamente | `model/Usuario.php` |
| Novo ID de sessão no login (anti session fixation) e logout que apaga sessão + cookie | `controller/UsuarioController.php`, `logout.php` |
| Conta bloqueada não entra (site e app) e cai se já estiver logada | `UsuarioController.php`, `api/login.php`, `api/_base.php` |
| IDOR: usuário só cancela/vê as próprias reservas (já existia, conferido nos testes) | `api/cancelar_reserva.php` |
| Erros do PHP não aparecem mais na tela (`display_errors` desligado no controller) | `controller/UsuarioController.php` |
| Consultas preparadas (mysqli) em todas as rotas novas; senha/hash nunca saem na API | `api/admin/*` |
| Arquivos auxiliares `_*.php` bloqueados para o navegador | `api/.htaccess` (vale para `api/admin/`) |

---

## 4. Arquivos

**Criados**

- `sistema/admin/index.php` — painel (mesmo visual do dashboard, usa `../dashboard.css`)
- `sistema/admin/admin.css` — só tabelas, selects e selos novos
- `sistema/api/admin/_admin.php` — base da API admin (exige admin, bloqueio de conta)
- `sistema/api/admin/resumo.php`, `usuarios.php`, `usuarios_acao.php`, `armarios.php`, `armarios_acao.php`
- `sistema/config/acesso.php` — proteção reutilizável das páginas
- `sistema/config/config.exemplo.php` — modelo de configuração (faltava no repositório)
- `sistema/sql/migracao_2026_11_admin.sql`

**Modificados**

- `sistema/controller/UsuarioController.php` — redireciona por perfil, recusa conta bloqueada, sessão só com o ID
- `sistema/model/Usuario.php` — cadastro sempre `usuario`
- `sistema/config/sessao.php` — token CSRF e `encerrarSessao()`
- `sistema/api/_base.php` — bloqueio, CSRF, `exigirAdmin()`
- `sistema/api/login.php` — recusa conta bloqueada (403)
- `sistema/api/_acesso.php` — chaveiro/QR de conta bloqueada não abre
- `sistema/dashboard.php` — admin vai para o painel; envia o token CSRF nas reservas/cancelamentos
- `sistema/login.php` — mensagem de conta bloqueada
- `sistema/logout.php` — encerra a sessão por completo

**App Flutter:** nenhuma alteração foi necessária. Ele continua usando as mesmas rotas;
uma conta bloqueada recebe a mensagem de erro no login e é deslogada se já estiver dentro.

---

## 5. Testes executados

Ambiente: PHP 8.3 + MariaDB 10.11, servidor embutido do PHP, com um banco montado a partir do código
(o repositório não tem o dump original), rodando as duas migrações. **77 verificações, 77 passaram.**

| # | Teste | Resultado |
|---|---|---|
| 1 | Cadastro pelo site com `perfil=admin` e `role=admin` forjados | Conta criada como `usuario` ✔ |
| 2 | Login comum → `dashboard.php`, ID de sessão trocado, senha errada recusada | ✔ |
| 3 | Conta virou admin no banco → login vai para `admin/`, painel abre, números corretos | ✔ |
| 4 | Usuário comum em `admin/`, `admin/index.php` e nas 5 rotas `api/admin/*` | 403, nada do painel exposto, nenhum armário criado ✔ |
| 5 | Cadastro pelo app com `perfil/role = admin`; token do app na API admin | `usuario`; 403 ✔ |
| 6 | Criar, repetir número, editar e remover armário; manutenção/liberar; manutenção com reserva ativa; editar usuário + chaveiro; chaveiro e e-mail repetidos; bloquear (cancela reserva, libera armário, derruba app e site, chaveiro nega); desbloquear; excluir conta sem histórico; admin não bloqueia/exclui a si mesmo; conta com histórico não é excluída | ✔ |
| 7 | App: cadastro, login, perfil, armários, reservar, minha reserva, QR; site: reservar com e sem CSRF, IDOR no cancelamento, histórico; ESP32 validando QR | ✔ |
| — | Tirar o perfil admin no banco corta o acesso na hora; logout | ✔ |

Também conferi o painel em tela de computador e de celular (capturas de tela).

**Não testado aqui (precisa ser feito por vocês):**

- O banco real do grupo e o da HostGator (só o banco reconstruído)
- O Apache da HostGator (o `.htaccess` não vale no servidor embutido do PHP)
- O app Flutter rodando no tablet (as rotas que ele usa foram testadas com requisições diretas)
- O ESP32 físico

---

## 6. Ainda falta (próxima fase)

- **Gerenciar reservas:** lista completa, busca, cancelar e criar reserva em nome de alguém
- **Histórico e estatísticas:** reservas concluídas/canceladas, uso por armário, gráficos
- **Log de acessos (RFID/QR)** no painel
