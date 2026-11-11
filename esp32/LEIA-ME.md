# Leitor RFID do SmartLocker (ESP32 + RC522)

O ESP32 **não decide** mais quem pode abrir. Ele lê o UID do chaveiro e pergunta ao servidor
(`sistema/api/validar_acesso.php`). O servidor confere no banco, grava a tentativa em
`logs_acesso` e responde se libera. O dashboard do usuário e o painel admin mostram a leitura
sozinhos em até 30 segundos.

## Ligações

| RC522 | ESP32 |
|-------|-------|
| SDA (SS) | GPIO 5 |
| SCK | GPIO 18 |
| MOSI | GPIO 23 |
| MISO | GPIO 19 |
| RST | GPIO 22 |
| 3.3V | 3V3 (**não** ligue no 5V) |
| GND | GND |

LED verde no GPIO 25 e LED vermelho no GPIO 26 (cada um com resistor de ~220 Ω).

## LEDs

| LED | Significado |
|-----|-------------|
| Verde aceso 3 s | Acesso liberado |
| Vermelho aceso 3 s | Acesso negado (chaveiro desconhecido, sem reserva, fora do horário, conta bloqueada...) |
| Vermelho piscando rápido | Sem Wi-Fi ou servidor fora do ar — o armário fica fechado |

## Como gravar

1. Arduino IDE com a placa **ESP32** instalada e a biblioteca **MFRC522** (Gerenciador de Bibliotecas).
2. Abra `smartlocker_rfid/smartlocker_rfid.ino`.
3. No topo do `.ino`, na parte **CONFIGURACAO**, preencha:
   - `WIFI_NOME` / `WIFI_SENHA` — rede **2.4 GHz**
   - `API_URL` — site no ar, ou o IP do computador com XAMPP (ex.: `http://192.168.0.10/devsphare/sistema/api/validar_acesso.php`)
   - `DEVICE_KEY` — a mesma frase do `sistema/config/config.local.php`
   - `NUMERO_ARMARIO` — número do armário onde o leitor fica
4. Placa "ESP32 Dev Module", porta COM certa, Upload. Abra o Monitor Serial em **115200**.

**Atenção:** antes de fazer commit, volte a senha do Wi-Fi e a `DEVICE_KEY` para os valores
de exemplo, para não publicar as senhas reais no GitHub.

## Testando com o XAMPP local

- O ESP32 e o computador precisam estar **na mesma rede**.
- `localhost` não funciona no ESP32: use o IP do computador (`ipconfig` → Endereço IPv4).
- Se o ESP32 não alcançar o computador, libere o Apache no Firewall do Windows.

## O armário precisa ser do tipo RFID

No painel admin → Armários, o armário do leitor precisa estar como **Chaveiro RFID**
(o Armário 01 já está). Se estiver como QR Code, o leitor recusa com o motivo `metodo_errado`.

## Cadastrar um chaveiro

1. Passe o chaveiro: o UID aparece no Monitor Serial (e fica em `logs_acesso` como "Chaveiro não cadastrado").
2. No painel admin → Usuários → editar → **Chaveiro RFID (UID)**, cole o UID e salve.
3. Faça uma reserva com esse usuário no armário do leitor. Passando o chaveiro de 10 min antes
   do início até o fim da reserva, o LED verde acende. Cada liberação alterna entrada/saída.

## Testar o servidor sem o ESP32

```bash
curl -X POST -H "X-Device-Key: SUA_CHAVE" -d "armario=1&metodo=rfid&codigo=A1B2C3D4" http://localhost/devsphare/sistema/api/validar_acesso.php
```
