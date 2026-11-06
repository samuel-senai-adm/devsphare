import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../api.dart';
import '../estado.dart';
import '../modelos.dart';
import '../tema.dart';
import '../widgets.dart';
import 'principal.dart';

/// QR Code da reserva ativa — é ele que o leitor do armário valida.
class TelaQrCode extends StatelessWidget {
  final void Function(int aba) irPara;

  const TelaQrCode({super.key, required this.irPara});

  Future<void> _cancelar(BuildContext context, Reserva r) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancelar reserva?'),
        content: Text('A reserva do ${r.armario} em ${formatarData(r.data)}, das ${r.inicio} às ${r.fim}, '
            'será cancelada e o QR Code deixará de abrir o armário.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Manter reserva')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(minimumSize: const Size(0, 44)),
            child: const Text('Cancelar reserva'),
          ),
        ],
      ),
    );
    if (confirmar != true || !context.mounted) return;

    try {
      await Escopo.ler(context).cancelar();
      if (context.mounted) mostrarMensagem(context, 'Reserva cancelada.');
    } on ApiException catch (e) {
      if (context.mounted) mostrarMensagem(context, e.mensagem, erro: true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final estado = Escopo.de(context);
    final r = estado.reserva;

    return RefreshIndicator(
      color: Cores.vermelho,
      onRefresh: estado.carregar,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          const Text('Meu QR Code', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
          const SizedBox(height: 2),
          const Text('Mostre no leitor do armário para abrir a porta.',
              style: TextStyle(fontSize: 14, color: Cores.cinza)),
          const SizedBox(height: 16),
          if (estado.erro != null) ...[
            AvisoErro(mensagem: estado.erro!, aoTentar: estado.carregar),
            const SizedBox(height: 14),
          ],
          if (!estado.carregadoUmaVez && estado.erro == null)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 60),
              child: Center(child: CircularProgressIndicator(color: Cores.vermelho)),
            )
          else if (r == null || r.qr == null)
            Painel(
              child: EstadoVazio(
                icone: Icons.qr_code_2,
                titulo: 'Nenhum QR Code ativo',
                texto: 'Faça uma reserva para gerar o seu QR Code de acesso.',
                rotuloAcao: 'Escolher armário',
                acao: () => irPara(Aba.armarios),
              ),
            )
          else ...[
            Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: _Ingresso(reserva: r),
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Cores.borda),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.memory, color: Cores.cinza, size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      estado.usuario?.chaveiroRfid == true
                          ? 'A porta libera a partir de 10 minutos antes do início. '
                              'Você também pode usar seu chaveiro RFID no lugar do QR.'
                          : 'A porta libera a partir de 10 minutos antes do início. '
                              'Aumente o brilho da tela se o leitor demorar.',
                      style: const TextStyle(fontSize: 13, color: Cores.texto2, height: 1.4),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () => _cancelar(context, r),
              style: OutlinedButton.styleFrom(foregroundColor: Cores.vermelhoEscuro),
              icon: const Icon(Icons.event_busy),
              label: const Text('Cancelar reserva'),
            ),
          ],
        ],
      ),
    );
  }
}

/// Cartão escuro tipo "ingresso": QR com cantos de mira vermelhos.
class _Ingresso extends StatelessWidget {
  final Reserva reserva;

  const _Ingresso({required this.reserva});

  @override
  Widget build(BuildContext context) {
    final r = reserva;
    return Container(
      padding: const EdgeInsets.fromLTRB(22, 26, 22, 22),
      decoration: BoxDecoration(
        color: Cores.grafite,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        children: [
          AspectRatio(
            aspectRatio: 1,
            child: CustomPaint(
              painter: _CantosMira(),
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16)),
                  child: QrImageView(
                    data: r.qr!,
                    version: QrVersions.auto,
                    errorCorrectionLevel: QrErrorCorrectLevel.M,
                    backgroundColor: Colors.white,
                    semanticsLabel: 'QR Code da reserva do ${r.armario}',
                    eyeStyle: const QrEyeStyle(eyeShape: QrEyeShape.square, color: Cores.grafite),
                    dataModuleStyle:
                        const QrDataModuleStyle(dataModuleShape: QrDataModuleShape.square, color: Cores.grafite),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                alignment: Alignment.center,
                decoration: BoxDecoration(color: Cores.vermelho, borderRadius: BorderRadius.circular(12)),
                child: Text(r.numero, style: estiloNumero(18, cor: Colors.white)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(r.armario,
                        style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w700)),
                    Text(r.localizacao, style: const TextStyle(color: Cores.cinza2, fontSize: 13)),
                  ],
                ),
              ),
              Selo.reserva(r),
            ],
          ),
          const SizedBox(height: 14),
          // linha picotada do ingresso
          Row(
            children: List.generate(
              24,
              (i) => Expanded(
                child: Container(
                  height: 1.5,
                  margin: const EdgeInsets.symmetric(horizontal: 2),
                  color: i.isEven ? Cores.grafite3 : Colors.transparent,
                ),
              ),
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              _Info(rotulo: 'Data', valor: formatarData(r.data)),
              _Info(rotulo: 'Início', valor: r.inicio),
              _Info(rotulo: 'Término', valor: r.fim),
            ],
          ),
        ],
      ),
    );
  }
}

class _Info extends StatelessWidget {
  final String rotulo;
  final String valor;

  const _Info({required this.rotulo, required this.valor});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(rotulo, style: const TextStyle(color: Cores.cinza2, fontSize: 12)),
          const SizedBox(height: 2),
          Text(valor, style: estiloNumero(15, cor: Colors.white)),
        ],
      ),
    );
  }
}

/// Os quatro cantos vermelhos em volta do QR (mesma ideia do site).
class _CantosMira extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final p = Paint()
      ..color = Cores.vermelho
      ..strokeWidth = 4
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;
    const l = 30.0;
    final w = size.width;
    final h = size.height;

    canvas.drawPath(Path()..moveTo(0, l)..lineTo(0, 0)..lineTo(l, 0), p);
    canvas.drawPath(Path()..moveTo(w - l, 0)..lineTo(w, 0)..lineTo(w, l), p);
    canvas.drawPath(Path()..moveTo(0, h - l)..lineTo(0, h)..lineTo(l, h), p);
    canvas.drawPath(Path()..moveTo(w - l, h)..lineTo(w, h)..lineTo(w, h - l), p);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
