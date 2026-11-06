import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'config.dart';
import 'modelos.dart';

/// Erro com mensagem pronta para mostrar ao usuário.
class ApiException implements Exception {
  final String mensagem;
  final int status;

  ApiException(this.mensagem, [this.status = 0]);

  @override
  String toString() => mensagem;
}

/// Fala com a API PHP. Guarda o token do login no aparelho
/// e envia em todas as chamadas no cabeçalho X-Auth-Token.
class Api {
  Api._();

  static final Api instancia = Api._();
  static const _chaveToken = 'smartlocker_token';

  String? _token;

  /// Chamado quando o servidor responde 401 (token vencido ou apagado).
  void Function()? aoExpirarSessao;

  bool get logado => _token != null;

  Future<bool> carregarToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_chaveToken);
    return _token != null;
  }

  Future<void> _salvarToken(String? token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    if (token == null) {
      await prefs.remove(_chaveToken);
    } else {
      await prefs.setString(_chaveToken, token);
    }
  }

  Future<Map<String, dynamic>> get(String rota) => _enviar(rota);

  Future<Map<String, dynamic>> post(String rota, [Map<String, dynamic>? corpo]) =>
      _enviar(rota, corpo: corpo ?? <String, dynamic>{}, metodoPost: true);

  Future<Map<String, dynamic>> _enviar(
    String rota, {
    Map<String, dynamic>? corpo,
    bool metodoPost = false,
  }) async {
    final uri = Uri.parse(apiBase + rota);
    final cabecalhos = <String, String>{
      'Accept': 'application/json',
      if (_token != null) 'X-Auth-Token': _token!,
      if (metodoPost) 'Content-Type': 'application/json',
    };

    http.Response resposta;
    try {
      resposta = metodoPost
          ? await http.post(uri, headers: cabecalhos, body: jsonEncode(corpo)).timeout(tempoLimite)
          : await http.get(uri, headers: cabecalhos).timeout(tempoLimite);
    } on TimeoutException {
      throw ApiException('O servidor demorou para responder. Tente de novo.');
    } catch (_) {
      throw ApiException('Sem conexão com o servidor. Confira o Wi-Fi e o endereço em lib/config.dart.');
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(resposta.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw ApiException('Resposta inesperada do servidor (código ${resposta.statusCode}). '
          'Confira o endereço em lib/config.dart.', resposta.statusCode);
    }

    if (resposta.statusCode == 401 && _token != null) {
      await _salvarToken(null);
      aoExpirarSessao?.call();
    }

    if (json['ok'] != true) {
      throw ApiException(
        (json['erro'] ?? 'Não foi possível concluir a ação.').toString(),
        resposta.statusCode,
      );
    }
    return json;
  }

  Future<Usuario> entrar(String email, String senha) async {
    final json = await post('login.php', {
      'email': email.trim(),
      'senha': senha,
      'dispositivo': 'App SmartLocker',
    });
    await _salvarToken(json['token'].toString());
    return Usuario.deJson(json['usuario'] as Map<String, dynamic>);
  }

  Future<Usuario> cadastrar({
    required String nome,
    required String email,
    required String senha,
    required String cpf,
    String telefone = '',
  }) async {
    final json = await post('cadastro.php', {
      'nome': nome.trim(),
      'email': email.trim(),
      'senha': senha,
      'cpf': cpf,
      'telefone': telefone,
      'dispositivo': 'App SmartLocker',
    });
    await _salvarToken(json['token'].toString());
    return Usuario.deJson(json['usuario'] as Map<String, dynamic>);
  }

  Future<void> sair() async {
    try {
      await post('logout.php');
    } catch (_) {
      // mesmo sem internet, o app esquece o login
    }
    await _salvarToken(null);
  }
}
