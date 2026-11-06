import 'package:flutter/material.dart';

import 'api.dart';
import 'modelos.dart';

/// Estado do app (dados vindos do banco pela API).
/// As telas leem daqui com  Escopo.de(context)  e redesenham quando muda.
class AppEstado extends ChangeNotifier {
  final Api _api = Api.instancia;

  Usuario? usuario;
  NumerosPerfil numeros = const NumerosPerfil();
  List<Armario> armarios = [];
  Reserva? reserva;
  List<Reserva> historico = []; // mais recentes primeiro
  List<Acesso> acessos = [];

  bool carregando = false;
  bool _descartado = false;
  bool carregadoUmaVez = false;
  String? erro;

  Future<void> carregar() async {
    if (carregando) return;
    carregando = true;
    notifyListeners();

    try {
      final respostas = await Future.wait([
        _api.get('armarios.php'),
        _api.get('minha_reserva.php'),
        _api.get('historico.php'),
        _api.get('perfil.php'),
      ]);

      armarios = (respostas[0]['armarios'] as List)
          .map((a) => Armario.deJson(a as Map<String, dynamic>))
          .toList();

      final r = respostas[1]['reserva'];
      reserva = r == null ? null : Reserva.deJson(r as Map<String, dynamic>);

      historico = (respostas[2]['reservas'] as List)
          .map((r) => Reserva.deJson(r as Map<String, dynamic>))
          .toList();
      acessos = ((respostas[2]['acessos'] as List?) ?? [])
          .map((a) => Acesso.deJson(a as Map<String, dynamic>))
          .toList();

      usuario = Usuario.deJson(respostas[3]['usuario'] as Map<String, dynamic>);
      numeros = NumerosPerfil.deJson(respostas[3]['numeros'] as Map<String, dynamic>);

      erro = null;
      carregadoUmaVez = true;
    } on ApiException catch (e) {
      erro = e.mensagem;
    } catch (e) {
      erro = 'Não foi possível ler os dados do servidor.';
    } finally {
      carregando = false;
      if (!_descartado) notifyListeners();
    }
  }

  @override
  void notifyListeners() {
    if (!_descartado) super.notifyListeners();
  }

  @override
  void dispose() {
    _descartado = true;
    super.dispose();
  }

  int get disponiveis => armarios.where((a) => a.livre).length;

  Future<Reserva> reservar({
    required Armario armario,
    required DateTime data,
    required TimeOfDay inicio,
    required TimeOfDay fim,
  }) async {
    final json = await _api.post('reservar.php', {
      'locker_id': armario.id,
      'data': '${data.year}-${_dois(data.month)}-${_dois(data.day)}',
      'inicio': '${_dois(inicio.hour)}:${_dois(inicio.minute)}',
      'fim': '${_dois(fim.hour)}:${_dois(fim.minute)}',
    });
    final nova = Reserva.deJson(json['reserva'] as Map<String, dynamic>);
    reserva = nova;
    notifyListeners();
    await carregar();
    return nova;
  }

  Future<void> cancelar() async {
    final atual = reserva;
    if (atual == null) return;
    await _api.post('cancelar_reserva.php', {'reserva_id': atual.id});
    reserva = null;
    notifyListeners();
    await carregar();
  }

  static String _dois(int n) => n.toString().padLeft(2, '0');
}

/// Disponibiliza o AppEstado para as telas abaixo dele.
class Escopo extends InheritedNotifier<AppEstado> {
  const Escopo({super.key, required AppEstado estado, required super.child}) : super(notifier: estado);

  /// Use no build(): redesenha a tela quando os dados mudam.
  static AppEstado de(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<Escopo>()!.notifier!;

  /// Use em botões e funções async: só lê, sem redesenhar.
  static AppEstado ler(BuildContext context) =>
      (context.getElementForInheritedWidgetOfExactType<Escopo>()!.widget as Escopo).notifier!;
}
