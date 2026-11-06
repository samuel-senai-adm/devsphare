# SmartLocker — atualização de 04/11/2026

> **Novo (06/11/2026):** painel administrativo e níveis de acesso. Veja [LEIA-ME-ADMIN.md](LEIA-ME-ADMIN.md).

O dashboard agora usa o **banco MySQL de verdade** (o localStorage saiu), e a pasta
`sistema/api/` virou uma API que atende **o site, o app Flutter e, depois, o ESP32**.

## Passo a passo para colocar no ar

### 1. Rodar a migração do banco (uma vez em cada banco)

phpMyAdmin → banco `devsphar_smartlocker` → aba **SQL** → cole o conteúdo de
`sistema/sql/migracao_2026_11.sql` → **Executar**.

Faça no banco local (XAMPP / servidor do SENAI) **e** no da hospedagem.
Ela só adiciona colunas e tabelas; nenhum dado é apagado.

### 2. Criar o arquivo de senhas (fora do GitHub)

Copie `sistema/config/config.exemplo.php` para `sistema/config/config.local.php` e preencha:

- dados do banco do lugar onde o site roda (cada computador/servidor tem o seu)
- `DEVICE_KEY`: uma frase longa qualquer; o ESP32 vai usar a mesma

O `config.local.php` está no `.gitignore`, então **não vai** para o GitHub.

### 3. Segurança — fazer hoje

A senha do banco de produção estava escrita no `conexao.php`, que é público no GitHub.

1. No cPanel → **Bancos de dados MySQL** → troque a senha do usuário `devsphar_adm`.
2. Coloque a senha nova só no `config.local.php` do servidor.
3. Tire do Git os arquivos que não deveriam estar lá (os arquivos continuam no seu PC):

```
git rm --cached backup.zip devsphare.zip sistema/error_log sistema/controller/error_log
git add .
git commit -m "API conectada ao banco, credenciais fora do Git"
git push
```

A senha antiga continua no histórico do GitHub; por isso o passo 1 (trocar a senha) é o que resolve.

### 4. Testar

1. Entre no site, reserve um armário, veja em **Minha reserva** e **Meu QR Code**.
2. No phpMyAdmin, a tabela `reservas` ganhou a linha e `lockers.status` virou `ocupado`.
3. Abra em outro navegador com outra conta: o armário aparece **Ocupado**.

## O que mudou

| Arquivo | O que faz agora |
|---|---|
| `sistema/dashboard.php` | Exige login, lê armários/reserva/histórico do banco, atualiza a cada 30 s |
| `sistema/api/*.php` | API JSON (tabela abaixo) |
| `sistema/config/conexao.php` | Lê as senhas de `config.local.php` |
| `sistema/config/sessao.php` | Corrige o erro de sessão da hospedagem que aparecia no `error_log` |
| `sistema/sql/migracao_2026_11.sql` | Ajustes no banco |
| `app_flutter/` | App do usuário (veja `app_flutter/LEIA-ME.md`) |

## Rotas da API

O site autentica pela sessão (login normal). O app manda o token do login no cabeçalho
`X-Auth-Token`. Toda resposta é JSON com `"ok": true` ou `"ok": false, "erro": "..."`.

| Rota | Método | Para quê |
|---|---|---|
| `login.php` | POST `email, senha` | Login do app, devolve `token` |
| `cadastro.php` | POST `nome, email, senha, cpf` | Cria conta pelo app |
| `logout.php` | POST | Encerra o login do app |
| `perfil.php` | GET | Dados da conta e números de reservas |
| `armarios.php` | GET | Armários com status `livre / ocupado / manutencao / minha` |
| `reservar.php` | POST `locker_id, data, inicio, fim` | Cria a reserva (já com QR) |
| `minha_reserva.php` | GET | Reserva ativa |
| `cancelar_reserva.php` | POST `reserva_id` | Cancela |
| `historico.php` | GET | Reservas + últimos acessos no armário |
| `gerar_qrcode.php` | GET | Conteúdo do QR da reserva ativa |
| `validar_acesso.php` | POST `armario, metodo, codigo` + cabeçalho `X-Device-Key` | **Para o ESP32**: diz se libera a porta |

Regras de reserva: uma ativa por pessoa e por armário, de 15 min a 12 h no mesmo dia,
até 30 dias à frente. Quando o horário termina, a reserva vira "Concluída" sozinha.

## Para o ESP32 (próxima etapa)

O leitor RC522 lê o UID do chaveiro → o ESP32 faz um POST para
`https://devsphare.com.br/sistema/api/validar_acesso.php` com:

```
X-Device-Key: <a DEVICE_KEY do config.local.php>
armario=1&metodo=rfid&codigo=A1B2C3D4
```

e a resposta traz `"liberado":true` ou `false` (com `"mensagem"` para mostrar num display).
Toda tentativa fica em `logs_acesso` e aparece na **Atividade recente** do usuário.

Para vincular um chaveiro a alguém (até existir o painel de admin): passe o chaveiro uma vez,
pegue o UID na coluna `detalhe` de `logs_acesso` e rode
`UPDATE usuarios SET rfid_uid = 'A1B2C3D4' WHERE email = 'aluno@exemplo.com';`

A porta libera de 10 minutos antes do início até o fim da reserva. A cada liberação o sistema
alterna entre "entrada" (guardar) e "saída" (retirar).
