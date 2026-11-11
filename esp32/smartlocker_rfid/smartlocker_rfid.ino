// =====================================================================
//  SMARTLOCKER DevSphare · Leitor RFID (ESP32 + RC522)
//
//  Lê o chaveiro e pergunta ao servidor se pode abrir:
//    POST api/validar_acesso.php   armario=1&metodo=rfid&codigo=<UID>
//
//  O servidor confere no banco (chaveiro do usuário, reserva ativa neste
//  armário, horário, conta bloqueada), grava a tentativa em logs_acesso
//  e responde "liberado": true/false. O dashboard do usuário e o painel
//  admin mostram a leitura na próxima atualização (até 30 s).
//
//  LEDs:
//    verde aceso 3 s         -> acesso liberado
//    vermelho aceso 3 s      -> acesso negado
//    vermelho piscando 5x    -> sem Wi-Fi ou servidor fora do ar (porta fica fechada)
//
//  Antes de gravar: preencha a CONFIGURACAO logo abaixo.
// =====================================================================

#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>

// ===============================
// CONFIGURACAO
// ===============================

// Wi-Fi (o ESP32 só conecta em rede 2.4 GHz)
#define WIFI_NOME   "nome-da-rede"
#define WIFI_SENHA  "senha-da-rede"

// Endereço da API
//  - Site no ar:  "https://devsphare.com.br/sistema/api/validar_acesso.php"
//  - XAMPP local: "http://192.168.0.10/devsphare/sistema/api/validar_acesso.php"
//    (troque pelo IP do computador; "localhost" NÃO funciona no ESP32)
#define API_URL     "https://devsphare.com.br/sistema/api/validar_acesso.php"

// Mesma frase da DEVICE_KEY do sistema/config/config.local.php
#define DEVICE_KEY  "troque-esta-chave-do-esp32"

// Número do armário onde este leitor está instalado (tabela lockers, coluna numero)
#define NUMERO_ARMARIO  1

// ===============================
// PINOS
// ===============================

#define SS_PIN    5
#define RST_PIN   22
#define SCK_PIN   18
#define MISO_PIN  19
#define MOSI_PIN  23

#define LED_VERDE     25
#define LED_VERMELHO  26

// ===============================
// TEMPOS
// ===============================

#define TEMPO_LED_MS        3000   // quanto tempo o LED fica aceso
#define TEMPO_REPETIR_MS    4000   // ignora o mesmo chaveiro encostado por esse tempo
#define TIMEOUT_HTTP_MS     6000   // espera máxima pela resposta do servidor
#define TEMPO_WIFI_MS      15000   // espera máxima para conectar no Wi-Fi

MFRC522 rfid(SS_PIN, RST_PIN);

String ultimoUID = "01020304";
unsigned long ultimaLeitura = 0;

// ===============================
// LEDS
// ===============================

void apagarLeds() {
  digitalWrite(LED_VERDE, LOW);
  digitalWrite(LED_VERMELHO, LOW);
}

void sinalLiberado() {
  digitalWrite(LED_VERMELHO, LOW);
  digitalWrite(LED_VERDE, HIGH);
  // >>> Quando tiver a trava (relé/solenoide), acione aqui. <<<
  delay(TEMPO_LED_MS);
  apagarLeds();
}

void sinalNegado() {
  digitalWrite(LED_VERDE, LOW);
  digitalWrite(LED_VERMELHO, HIGH);
  delay(TEMPO_LED_MS);
  apagarLeds();
}

void sinalErro() {
  digitalWrite(LED_VERDE, LOW);
  for (int i = 0; i < 5; i++) {
    digitalWrite(LED_VERMELHO, HIGH);
    delay(150);
    digitalWrite(LED_VERMELHO, LOW);
    delay(150);
  }
}

// ===============================
// WI-FI
// ===============================

String tipoSenha(wifi_auth_mode_t tipo) {
  switch (tipo) {
    case WIFI_AUTH_OPEN:            return "aberta (sem senha)";
    case WIFI_AUTH_WEP:             return "WEP";
    case WIFI_AUTH_WPA_PSK:         return "WPA";
    case WIFI_AUTH_WPA2_PSK:        return "WPA2";
    case WIFI_AUTH_WPA_WPA2_PSK:    return "WPA/WPA2";
    case WIFI_AUTH_WPA2_ENTERPRISE: return "WPA2-Enterprise (login + senha)";
    case WIFI_AUTH_WPA3_PSK:        return "WPA3";
    case WIFI_AUTH_WPA2_WPA3_PSK:   return "WPA2/WPA3";
    default:                        return "outro";
  }
}

// Procura as redes ao redor e explica por que não conectou
void diagnosticoWiFi() {
  wl_status_t status = WiFi.status();
  Serial.print("Status do Wi-Fi: ");
  Serial.print(status);
  if (status == WL_NO_SSID_AVAIL)  Serial.print(" (rede nao encontrada)");
  if (status == WL_CONNECT_FAILED) Serial.print(" (senha errada ou rede recusou)");
  if (status == WL_DISCONNECTED)   Serial.print(" (nao terminou de conectar)");
  Serial.println();

  Serial.println("Procurando redes ao redor...");
  WiFi.disconnect();
  int total = WiFi.scanNetworks();

  bool achou = false;
  for (int i = 0; i < total; i++) {
    bool ehAMinha = WiFi.SSID(i) == WIFI_NOME;
    if (ehAMinha) achou = true;

    Serial.print(ehAMinha ? " >> " : "    ");
    Serial.print("\"");
    Serial.print(WiFi.SSID(i));
    Serial.print("\"  sinal ");
    Serial.print(WiFi.RSSI(i));
    Serial.print(" dBm  canal ");
    Serial.print(WiFi.channel(i));
    Serial.print("  ");
    Serial.println(tipoSenha(WiFi.encryptionType(i)));

    if (ehAMinha && WiFi.encryptionType(i) == WIFI_AUTH_WPA2_ENTERPRISE) {
      Serial.println("    -> Rede com login de usuario (tipo rede da escola): este codigo nao suporta.");
      Serial.println("       Use um roteador proprio ou o roteador do celular.");
    }
    if (ehAMinha && WiFi.RSSI(i) < -80) {
      Serial.println("    -> Sinal muito fraco: aproxime o ESP32 do roteador.");
    }
  }

  if (total <= 0) {
    Serial.println("Nenhuma rede encontrada (antena/placa com problema?).");
  } else if (!achou) {
    Serial.println("A rede \"" WIFI_NOME "\" NAO apareceu na lista. Causas comuns:");
    Serial.println("  - a rede e 5 GHz (o ESP32 so enxerga 2.4 GHz)");
    Serial.println("  - roteador do celular: ative 'Maximizar compatibilidade' (iPhone)");
    Serial.println("    ou 'Banda 2.4 GHz' (Android)");
    Serial.println("  - nome diferente: confira maiusculas, minusculas e espacos");
  } else {
    Serial.println("A rede foi encontrada: se o status for 4, a senha esta errada.");
  }
  WiFi.scanDelete();
}

bool conectarWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return true;
  }

  Serial.print("Conectando no Wi-Fi \"");
  Serial.print(WIFI_NOME);
  Serial.print("\"");

  WiFi.mode(WIFI_STA);
  WiFi.disconnect();          // limpa tentativa anterior que travou
  WiFi.setAutoReconnect(true);
  WiFi.setSleep(false);       // sem economia de energia: responde bem mais rápido
  WiFi.begin(WIFI_NOME, WIFI_SENHA);

  unsigned long inicio = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - inicio < TEMPO_WIFI_MS) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Nao conectou no Wi-Fi.");
    diagnosticoWiFi();
    return false;
  }

  Serial.print("Wi-Fi conectado. IP do ESP32: ");
  Serial.println(WiFi.localIP());
  return true;
}

// ===============================
// LER UID
// ===============================

String lerUID() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) {
      uid += "0";
    }
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

// ===============================
// JSON SIMPLES
// Pega o texto de um campo da resposta, ex.: "mensagem":"Acesso liberado..."
// ===============================

String campoJson(const String& json, const char* nome) {
  String chave = "\"" + String(nome) + "\":";
  int pos = json.indexOf(chave);
  if (pos < 0) {
    return "";
  }
  pos += chave.length();

  while (pos < (int)json.length() && json[pos] == ' ') {
    pos++;
  }

  // valor sem aspas (true, false, número)
  if (json[pos] != '"') {
    int fim = pos;
    while (fim < (int)json.length() && json[fim] != ',' && json[fim] != '}') {
      fim++;
    }
    return json.substring(pos, fim);
  }

  // valor entre aspas
  String valor = "";
  for (int i = pos + 1; i < (int)json.length(); i++) {
    char c = json[i];
    if (c == '\\' && i + 1 < (int)json.length()) {
      valor += json[++i];
    } else if (c == '"') {
      break;
    } else {
      valor += c;
    }
  }
  return valor;
}

// ===============================
// PERGUNTAR AO SERVIDOR
// Retorna:  1 = liberado,  0 = negado,  -1 = erro (sem rede / servidor)
// ===============================

int validarNoServidor(const String& uid) {
  if (!conectarWiFi()) {
    return -1;
  }

  String url = API_URL;
  bool https = url.startsWith("https");
  unsigned long inicio = millis();

  // Clientes "static": a conexão é reaproveitada entre leituras próximas
  // (evita refazer a conexão / o HTTPS toda vez)
  static WiFiClient clienteHttp;
  static WiFiClientSecure clienteHttps;
  static bool configurado = false;
  if (!configurado) {
    // Não confere o certificado do site (simples para o projeto).
    // A DEVICE_KEY continua indo criptografada pelo HTTPS.
    clienteHttps.setInsecure();
    configurado = true;
  }

  HTTPClient http;
  http.setReuse(true);
  http.setTimeout(TIMEOUT_HTTP_MS);
  http.setConnectTimeout(TIMEOUT_HTTP_MS);

  bool iniciou = https ? http.begin(clienteHttps, url) : http.begin(clienteHttp, url);
  if (!iniciou) {
    Serial.println("Erro: API_URL invalida na CONFIGURACAO");
    return -1;
  }

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  http.addHeader("X-Device-Key", DEVICE_KEY);

  String corpo = "armario=" + String(NUMERO_ARMARIO) + "&metodo=rfid&codigo=" + uid;

  Serial.println("Consultando servidor...");
  int status = http.POST(corpo);

  if (status <= 0) {
    Serial.print("Falha na conexao com o servidor: ");
    Serial.println(http.errorToString(status));
    http.end();
    return -1;
  }

  String resposta = http.getString();
  http.end();

  Serial.print("Resposta em ");
  Serial.print(millis() - inicio);
  Serial.println(" ms");

  if (status != 200) {
    Serial.print("Servidor respondeu HTTP ");
    Serial.print(status);
    Serial.print(": ");
    String erro = campoJson(resposta, "erro");
    Serial.println(erro != "" ? erro : resposta);
    if (status == 401) {
      Serial.println("-> Confira se a DEVICE_KEY do sketch e igual a do config.local.php");
    }
    return -1;
  }

  bool liberado = campoJson(resposta, "liberado") == "true";
  String mensagem = campoJson(resposta, "mensagem");
  String armario = campoJson(resposta, "armario");

  if (armario != "") {
    Serial.print("Armario: ");
    Serial.println(armario);
  }
  Serial.print("Servidor: ");
  Serial.println(mensagem);

  if (!liberado) {
    Serial.print("Motivo: ");
    Serial.println(campoJson(resposta, "motivo"));
  }

  return liberado ? 1 : 0;
}

// ===============================
// SETUP
// ===============================

void setup() {
  Serial.begin(115200);

  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_VERMELHO, OUTPUT);
  apagarLeds();

  SPI.begin(SCK_PIN, MISO_PIN, MOSI_PIN, SS_PIN);
  rfid.PCD_Init();

  Serial.println();
  Serial.println("================================");
  Serial.println("       SMARTLOCKER DevSphare");
  Serial.println("================================");
  Serial.print("Armario deste leitor: ");
  Serial.println(NUMERO_ARMARIO);

  // Confere se o RC522 respondeu (0x00 ou 0xFF = fio solto / sem energia)
  byte versao = rfid.PCD_ReadRegister(MFRC522::VersionReg);
  if (versao == 0x00 || versao == 0xFF) {
    Serial.println("ATENCAO: RC522 nao encontrado. Confira os fios e o 3.3V.");
  }

  conectarWiFi();

  Serial.println("Aproxime o chaveiro...");
}

// ===============================
// LOOP PRINCIPAL
// ===============================

void loop() {

  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  String uid = lerUID();

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  // Mesmo chaveiro ainda encostado: não manda de novo pro servidor
  if (uid == ultimoUID && millis() - ultimaLeitura < TEMPO_REPETIR_MS) {
    return;
  }

  Serial.println();
  Serial.println("--------------------------------");
  Serial.print("Chaveiro detectado! UID: ");
  Serial.println(uid);

  int resultado = validarNoServidor(uid);

  if (resultado == 1) {
    Serial.println(">>> ACESSO PERMITIDO <<<");
    sinalLiberado();
  } else if (resultado == 0) {
    Serial.println(">>> ACESSO NEGADO <<<");
    sinalNegado();
  } else {
    Serial.println(">>> SEM CONEXAO - ARMARIO PERMANECE FECHADO <<<");
    sinalErro();
  }

  Serial.println("--------------------------------");
  Serial.println("Aguardando proximo chaveiro...");

  ultimoUID = uid;
  ultimaLeitura = millis();
}
