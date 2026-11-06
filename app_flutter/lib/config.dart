/// Endereço da API do SmartLocker (a pasta sistema/api do site).
///
/// TESTANDO NO XAMPP (tablet e computador no MESMO Wi-Fi):
///   1. No computador, abra o Prompt e rode  ipconfig  -> anote o "Endereço IPv4" (ex.: 192.168.0.15)
///   2. Troque abaixo para:  'http://192.168.0.15/devsphare/sistema/api/'
///      (o caminho depois do IP é a pasta do projeto dentro de C:\xampp\htdocs)
///   3. NÃO use "localhost": no tablet, localhost é o próprio tablet.
///
/// SITE NO AR:
///   'https://devsphare.com.br/sistema/api/'
///
/// O endereço precisa terminar com barra  /
const String apiBase = 'http://10.140.169.21/devsphare/sistema/api/';

/// Tempo máximo de espera por uma resposta do servidor.
const Duration tempoLimite = Duration(seconds: 15);

/// De quanto em quanto tempo o app busca novidades no servidor
/// (outra pessoa reservou, o leitor abriu a porta etc.).
const Duration intervaloAtualizacao = Duration(seconds: 30);
