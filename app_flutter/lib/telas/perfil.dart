import 'package:flutter/material.dart';

import '../config.dart';
import '../estado.dart';
import '../tema.dart';
import '../widgets.dart';

class TelaPerfil extends StatelessWidget {
  final Future<void> Function() aoSair;

  const TelaPerfil({super.key, required this.aoSair});

  Future<void> _confirmarSaida(BuildContext context) async {
    final sair = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sair da conta?'),
        content: const Text('Você vai precisar entrar de novo com e-mail e senha.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Ficar')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
            child: const Text('Sair'),
          ),
        ],
      ),
    );
    if (sair == true) await aoSair();
  }

  @override
  Widget build(BuildContext context) {
    final estado = Escopo.de(context);
    final u = estado.usuario;
    final n = estado.numeros;

    return RefreshIndicator(
      color: Cores.vermelho,
      onRefresh: estado.carregar,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          const Text('Meu perfil', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
          const SizedBox(height: 16),
          if (estado.erro != null) ...[
            AvisoErro(mensagem: estado.erro!, aoTentar: estado.carregar),
            const SizedBox(height: 12),
          ],
          Painel(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 60,
                      height: 60,
                      alignment: Alignment.center,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(colors: [Cores.vermelho, Cores.vermelhoProfundo]),
                      ),
                      child: Text(u?.inicial ?? '',
                          style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w700)),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(u?.nome ?? '…', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                          Text(
                            u == null
                                ? ''
                                : '${u.tipoConta} SmartLocker'
                                    '${u.clienteDesde == null ? '' : ' desde ${formatarData(u.clienteDesde!)}'}',
                            style: const TextStyle(fontSize: 13, color: Cores.cinza),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Divider(color: Cores.cinzaClaro, height: 1),
                _Linha(icone: Icons.mail_outline, rotulo: 'E-mail', valor: u?.email ?? '-'),
                _Linha(
                  icone: Icons.contactless_outlined,
                  rotulo: 'Chaveiro RFID',
                  valor: u?.chaveiroRfid == true ? 'Vinculado' : 'Não vinculado',
                  corValor: u?.chaveiroRfid == true ? Cores.verde : null,
                ),
                if ((u?.telefone ?? '').isNotEmpty) _Linha(icone: Icons.phone_outlined, rotulo: 'Telefone', valor: u!.telefone!),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              _Numero(valor: n.total, rotulo: 'Reservas'),
              const SizedBox(width: 10),
              _Numero(valor: n.concluidas, rotulo: 'Concluídas'),
              const SizedBox(width: 10),
              _Numero(valor: n.canceladas, rotulo: 'Canceladas'),
            ],
          ),
          const SizedBox(height: 20),
          OutlinedButton.icon(
            onPressed: () => _confirmarSaida(context),
            style: OutlinedButton.styleFrom(foregroundColor: Cores.vermelhoEscuro),
            icon: const Icon(Icons.logout),
            label: const Text('Sair da conta'),
          ),
          const SizedBox(height: 18),
          Center(
            child: Text('Servidor: $apiBase',
                textAlign: TextAlign.center, style: const TextStyle(fontSize: 11, color: Cores.cinza2)),
          ),
        ],
      ),
    );
  }
}

class _Linha extends StatelessWidget {
  final IconData icone;
  final String rotulo;
  final String valor;
  final Color? corValor;

  const _Linha({required this.icone, required this.rotulo, required this.valor, this.corValor});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 14),
      child: Row(
        children: [
          Icon(icone, size: 20, color: Cores.cinza),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(rotulo, style: const TextStyle(fontSize: 12, color: Cores.cinza)),
                Text(valor,
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: corValor ?? Cores.texto)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Numero extends StatelessWidget {
  final int valor;
  final String rotulo;

  const _Numero({required this.valor, required this.rotulo});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Painel(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        child: Column(
          children: [
            Text('$valor', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700)),
            Text(rotulo, style: const TextStyle(fontSize: 12, color: Cores.cinza)),
          ],
        ),
      ),
    );
  }
}
