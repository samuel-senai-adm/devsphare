import 'dart:async';

import 'package:flutter/material.dart';

import '../api.dart';
import '../config.dart';
import '../estado.dart';
import '../tema.dart';
import 'armarios.dart';
import 'historico.dart';
import 'inicio.dart';
import 'login.dart';
import 'perfil.dart';
import 'qrcode.dart';

/// Abas do app
class Aba {
  static const inicio = 0;
  static const armarios = 1;
  static const qrcode = 2;
  static const historico = 3;
  static const perfil = 4;
}

/// Tela principal: navegação inferior (celular) ou lateral (tablet).
class TelaPrincipal extends StatefulWidget {
  const TelaPrincipal({super.key});

  @override
  State<TelaPrincipal> createState() => _TelaPrincipalState();
}

class _TelaPrincipalState extends State<TelaPrincipal> with WidgetsBindingObserver {
  final AppEstado _estado = AppEstado();
  int _aba = Aba.inicio;
  Timer? _timer;

  static const _destinos = [
    (Icons.home_outlined, Icons.home, 'Início'),
    (Icons.grid_view_outlined, Icons.grid_view_rounded, 'Armários'),
    (Icons.qr_code_2_outlined, Icons.qr_code_2, 'QR Code'),
    (Icons.history_outlined, Icons.history, 'Histórico'),
    (Icons.person_outline, Icons.person, 'Perfil'),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _estado.carregar();
    _timer = Timer.periodic(intervaloAtualizacao, (_) => _estado.carregar());
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _timer?.cancel();
    _estado.dispose();
    super.dispose();
  }

  /// Ao voltar para o app, busca o estado atualizado.
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _estado.carregar();
  }

  void irPara(int aba) => setState(() => _aba = aba);

  Future<void> sair() async {
    await Api.instancia.sair();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const TelaLogin()),
      (_) => false,
    );
  }

  Widget _pagina() {
    switch (_aba) {
      case Aba.armarios:
        return TelaArmarios(irPara: irPara);
      case Aba.qrcode:
        return TelaQrCode(irPara: irPara);
      case Aba.historico:
        return const TelaHistorico();
      case Aba.perfil:
        return TelaPerfil(aoSair: sair);
      default:
        return TelaInicio(irPara: irPara);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Escopo(
      estado: _estado,
      child: LayoutBuilder(builder: (context, medidas) {
        final largo = medidas.maxWidth >= 760;

        return Scaffold(
          body: SafeArea(
            bottom: false,
            child: Row(
              children: [
                if (largo) _trilhoLateral(medidas.maxWidth >= 1100),
                Expanded(
                  child: AnimatedSwitcher(
                    duration: const Duration(milliseconds: 180),
                    child: KeyedSubtree(key: ValueKey(_aba), child: _pagina()),
                  ),
                ),
              ],
            ),
          ),
          bottomNavigationBar: largo ? null : _barraInferior(),
        );
      }),
    );
  }

  Widget _barraInferior() {
    return AnimatedBuilder(
      animation: _estado,
      builder: (context, _) => NavigationBar(
        selectedIndex: _aba,
        onDestinationSelected: irPara,
        backgroundColor: Colors.white,
        indicatorColor: Cores.vermelhoSuave,
        destinations: [
          for (var i = 0; i < _destinos.length; i++)
            NavigationDestination(
              icon: _iconeComAviso(i, Icon(_destinos[i].$1)),
              selectedIcon: _iconeComAviso(i, Icon(_destinos[i].$2, color: Cores.vermelho)),
              label: _destinos[i].$3,
            ),
        ],
      ),
    );
  }

  /// Bolinha vermelha no QR Code quando há reserva ativa.
  Widget _iconeComAviso(int indice, Widget icone) {
    if (indice != Aba.qrcode || _estado.reserva == null) return icone;
    return Badge(smallSize: 9, backgroundColor: Cores.vermelho, child: icone);
  }

  Widget _trilhoLateral(bool expandido) {
    return AnimatedBuilder(
      animation: _estado,
      builder: (context, _) => Container(
        color: Cores.grafite,
        child: NavigationRail(
          backgroundColor: Cores.grafite,
          extended: expandido,
          minExtendedWidth: 220,
          selectedIndex: _aba,
          onDestinationSelected: irPara,
          indicatorColor: const Color(0x33E11D2E),
          selectedIconTheme: const IconThemeData(color: Colors.white),
          unselectedIconTheme: const IconThemeData(color: Cores.cinza2),
          selectedLabelTextStyle: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
          unselectedLabelTextStyle: const TextStyle(color: Cores.cinza2),
          labelType: expandido ? NavigationRailLabelType.none : NavigationRailLabelType.all,
          leading: Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: Cores.vermelho,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.lock, color: Colors.white, size: 20),
                ),
                if (expandido) ...[
                  const SizedBox(width: 10),
                  const Text('SmartLocker',
                      style: TextStyle(color: Colors.white, fontSize: 17, fontWeight: FontWeight.w700)),
                ],
              ],
            ),
          ),
          destinations: [
            for (var i = 0; i < _destinos.length; i++)
              NavigationRailDestination(
                icon: _iconeComAviso(i, Icon(_destinos[i].$1)),
                selectedIcon: _iconeComAviso(i, Icon(_destinos[i].$2)),
                label: Text(_destinos[i].$3),
              ),
          ],
        ),
      ),
    );
  }
}
