import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'api.dart';
import 'tema.dart';
import 'telas/login.dart';
import 'telas/principal.dart';

/// SmartLocker — aplicativo do usuário (DevSphare).
/// Usa a mesma API do site (sistema/api). Endereço em lib/config.dart.
Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final logado = await Api.instancia.carregarToken();
  runApp(SmartLockerApp(logado: logado));
}

final _navegador = GlobalKey<NavigatorState>();

class SmartLockerApp extends StatefulWidget {
  final bool logado;

  const SmartLockerApp({super.key, required this.logado});

  @override
  State<SmartLockerApp> createState() => _SmartLockerAppState();
}

class _SmartLockerAppState extends State<SmartLockerApp> {
  @override
  void initState() {
    super.initState();
    // Token vencido ou apagado no servidor: volta para o login.
    Api.instancia.aoExpirarSessao = () {
      _navegador.currentState?.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const TelaLogin(aviso: 'Sua sessão expirou. Entre novamente.')),
        (_) => false,
      );
    };
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SmartLocker',
      debugShowCheckedModeBanner: false,
      navigatorKey: _navegador,
      theme: temaSmartLocker(),
      locale: const Locale('pt', 'BR'),
      supportedLocales: const [Locale('pt', 'BR')],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      home: widget.logado ? const TelaPrincipal() : const TelaLogin(),
    );
  }
}
