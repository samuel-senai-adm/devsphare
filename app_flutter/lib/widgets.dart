import 'package:flutter/material.dart';

import 'modelos.dart';
import 'tema.dart';

/* ===================== FORMATAÇÃO ===================== */

String doisDigitos(int n) => n.toString().padLeft(2, '0');

/// '2026-11-04' -> '04/11/2026'
String formatarData(String iso) {
  final p = iso.split('-');
  return p.length == 3 ? '${p[2]}/${p[1]}/${p[0]}' : iso;
}

String formatarDataCurta(DateTime d) => '${doisDigitos(d.day)}/${doisDigitos(d.month)}';

String formatarDataHora(DateTime d) =>
    '${formatarDataCurta(d)} às ${doisDigitos(d.hour)}:${doisDigitos(d.minute)}';

const _diasSemana = ['segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado', 'domingo'];
const _meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

String dataPorExtenso(DateTime d) {
  final texto = '${_diasSemana[d.weekday - 1]}, ${d.day} de ${_meses[d.month - 1]}';
  return texto[0].toUpperCase() + texto.substring(1);
}

String saudacao(DateTime d) => d.hour < 12 ? 'Bom dia' : (d.hour < 18 ? 'Boa tarde' : 'Boa noite');

/// 135 min -> "2h 15min"
String textoTempo(Duration duracao) {
  final total = duracao.inMinutes < 0 ? 0 : duracao.inMinutes;
  final d = total ~/ 1440;
  final h = (total % 1440) ~/ 60;
  final m = total % 60;
  final partes = <String>[];
  if (d > 0) partes.add(d == 1 ? '1 dia' : '$d dias');
  if (h > 0) partes.add('${h}h');
  if (m > 0 || partes.isEmpty) partes.add('${m}min');
  return partes.join(' ');
}

String textoDuracao(int minutos) {
  final h = minutos ~/ 60;
  final m = minutos % 60;
  if (h > 0 && m > 0) return '${h}h ${m}min';
  if (h > 0) return h == 1 ? '1 hora' : '$h horas';
  return '$m min';
}

/* ===================== PAINEL ===================== */

class Painel extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;

  const Painel({super.key, required this.child, this.padding = const EdgeInsets.all(18)});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Cores.borda),
      ),
      child: child,
    );
  }
}

class TituloSecao extends StatelessWidget {
  final String titulo;
  final String? subtitulo;
  final Widget? acao;

  const TituloSecao({super.key, required this.titulo, this.subtitulo, this.acao});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(titulo, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
              if (subtitulo != null)
                Padding(
                  padding: const EdgeInsets.only(top: 2),
                  child: Text(subtitulo!, style: const TextStyle(fontSize: 13, color: Cores.cinza)),
                ),
            ],
          ),
        ),
        if (acao != null) acao!,
      ],
    );
  }
}

/* ===================== SELO DE STATUS ===================== */

class Selo extends StatelessWidget {
  final String texto;
  final Color fundo;
  final Color cor;
  final IconData? icone;

  const Selo({super.key, required this.texto, required this.fundo, required this.cor, this.icone});

  factory Selo.armario(String status) {
    switch (status) {
      case 'livre':
        return const Selo(texto: 'Disponível', fundo: Cores.verdeSuave, cor: Cores.verde);
      case 'minha':
        return const Selo(texto: 'Minha reserva', fundo: Cores.vermelhoSuave, cor: Cores.vermelhoEscuro);
      case 'manutencao':
        return const Selo(texto: 'Manutenção', fundo: Cores.ambarSuave, cor: Cores.ambar);
      default:
        return const Selo(texto: 'Ocupado', fundo: Cores.cinzaClaro, cor: Cores.cinza);
    }
  }

  factory Selo.reserva(Reserva r) {
    switch (r.statusCodigo) {
      case 'finalizada':
        return const Selo(texto: 'Concluída', fundo: Cores.verdeSuave, cor: Cores.verde, icone: Icons.check_circle);
      case 'cancelada':
        return const Selo(texto: 'Cancelada', fundo: Cores.cinzaClaro, cor: Cores.cinza, icone: Icons.event_busy);
      default:
        return Selo(
          texto: r.emAndamento ? 'Em uso' : 'Agendada',
          fundo: Cores.vermelhoSuave,
          cor: Cores.vermelhoEscuro,
          icone: Icons.lock,
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: fundo, borderRadius: BorderRadius.circular(999)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icone != null) ...[
            Icon(icone, size: 13, color: cor),
            const SizedBox(width: 4),
          ],
          Text(texto, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: cor)),
        ],
      ),
    );
  }
}

/* ===================== LED ===================== */

class Led extends StatelessWidget {
  final Color cor;
  final double tamanho;

  const Led({super.key, required this.cor, this.tamanho = 8});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: tamanho,
      height: tamanho,
      decoration: BoxDecoration(
        color: cor,
        shape: BoxShape.circle,
        boxShadow: [BoxShadow(color: cor, blurRadius: tamanho * 0.9)],
      ),
    );
  }
}

Color corLed(String status) {
  switch (status) {
    case 'livre':
      return Cores.verdeLed;
    case 'minha':
      return Colors.white;
    case 'manutencao':
      return const Color(0xFFF59E0B);
    default:
      return Cores.cinza2;
  }
}

/* ===================== PORTA DO ARMÁRIO ===================== */

/// O cartão do armário desenhado como uma porta: número na placa,
/// LED de situação, frestas de ventilação e maçaneta.
class PortaArmario extends StatelessWidget {
  final Armario armario;
  final VoidCallback? aoTocar;
  final String rotuloBotao;

  const PortaArmario({super.key, required this.armario, this.aoTocar, required this.rotuloBotao});

  @override
  Widget build(BuildContext context) {
    final minha = armario.minha;
    final livre = armario.livre;
    final textoCor = minha ? Colors.white : Cores.texto;
    final subCor = minha ? Cores.brancoMeio : Cores.cinza;
    final fresta = minha ? const Color(0x55FFFFFF) : Cores.borda;

    return Semantics(
      button: aoTocar != null,
      label: '${armario.nome}, ${armario.localizacao}, ${Selo.armario(armario.status).texto}',
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: minha ? Cores.vermelhoSuave : Cores.borda, width: minha ? 2 : 1),
        ),
        padding: const EdgeInsets.all(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  gradient: minha
                      ? const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Cores.vermelho, Cores.vermelhoProfundo],
                        )
                      : const LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [Color(0xFFF8F9FA), Color(0xFFEDEFF2)],
                        ),
                  border: minha ? null : Border.all(color: Cores.borda),
                ),
                child: Stack(
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: minha ? const Color(0x33FFFFFF) : Colors.white,
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: minha ? const Color(0x55FFFFFF) : Cores.borda),
                              ),
                              child: Text(armario.numero, style: estiloNumero(13, cor: textoCor)),
                            ),
                            const Spacer(),
                            Led(cor: corLed(armario.status)),
                          ],
                        ),
                        const SizedBox(height: 10),
                        for (var i = 0; i < 3; i++)
                          Container(
                            width: 30,
                            height: 2.5,
                            margin: const EdgeInsets.only(bottom: 4),
                            decoration: BoxDecoration(color: fresta, borderRadius: BorderRadius.circular(2)),
                          ),
                        const Spacer(),
                        Text(armario.nome,
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: textoCor)),
                        Row(
                          children: [
                            Icon(Icons.place, size: 13, color: subCor),
                            const SizedBox(width: 2),
                            Flexible(
                              child: Text(armario.localizacao,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(fontSize: 12, color: subCor)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        minha
                            ? Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                    color: Colors.white, borderRadius: BorderRadius.circular(999)),
                                child: const Text('Minha reserva',
                                    style: TextStyle(
                                        fontSize: 12, fontWeight: FontWeight.w700, color: Cores.vermelhoEscuro)),
                              )
                            : Selo.armario(armario.status),
                      ],
                    ),
                    // maçaneta
                    Positioned(
                      right: 0,
                      top: 0,
                      bottom: 0,
                      child: Center(
                        child: Container(
                          width: 4,
                          height: 30,
                          decoration: BoxDecoration(
                            color: minha ? const Color(0x99FFFFFF) : Cores.cinza2,
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              height: 42,
              child: minha
                  ? FilledButton.icon(
                      onPressed: aoTocar,
                      style: FilledButton.styleFrom(
                          backgroundColor: Cores.grafite, minimumSize: const Size(0, 42)),
                      icon: const Icon(Icons.qr_code_2, size: 18),
                      label: Text(rotuloBotao),
                    )
                  : livre
                      ? FilledButton.icon(
                          onPressed: aoTocar,
                          style: FilledButton.styleFrom(minimumSize: const Size(0, 42)),
                          icon: const Icon(Icons.event_available, size: 18),
                          label: Text(rotuloBotao),
                        )
                      : OutlinedButton.icon(
                          onPressed: null,
                          style: OutlinedButton.styleFrom(minimumSize: const Size(0, 42)),
                          icon: const Icon(Icons.block, size: 18),
                          label: Text(rotuloBotao),
                        ),
            ),
          ],
        ),
      ),
    );
  }
}

/* ===================== ESTADO VAZIO / ERRO ===================== */

class EstadoVazio extends StatelessWidget {
  final IconData icone;
  final String titulo;
  final String texto;
  final String? rotuloAcao;
  final VoidCallback? acao;

  const EstadoVazio({
    super.key,
    required this.icone,
    required this.titulo,
    required this.texto,
    this.rotuloAcao,
    this.acao,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(color: Cores.vermelhoSuave, borderRadius: BorderRadius.circular(18)),
            child: Icon(icone, color: Cores.vermelho, size: 28),
          ),
          const SizedBox(height: 14),
          Text(titulo, textAlign: TextAlign.center, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700)),
          const SizedBox(height: 6),
          Text(texto, textAlign: TextAlign.center, style: const TextStyle(fontSize: 14, color: Cores.cinza)),
          if (rotuloAcao != null && acao != null) ...[
            const SizedBox(height: 18),
            FilledButton(onPressed: acao, child: Text(rotuloAcao!)),
          ],
        ],
      ),
    );
  }
}

/// Faixa de aviso quando o servidor não respondeu.
class AvisoErro extends StatelessWidget {
  final String mensagem;
  final VoidCallback aoTentar;

  const AvisoErro({super.key, required this.mensagem, required this.aoTentar});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(14, 10, 6, 10),
      decoration: BoxDecoration(
        color: Cores.ambarSuave,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          const Icon(Icons.wifi_off, color: Cores.ambar, size: 20),
          const SizedBox(width: 10),
          Expanded(child: Text(mensagem, style: const TextStyle(fontSize: 13, color: Cores.texto))),
          TextButton(onPressed: aoTentar, child: const Text('Tentar de novo')),
        ],
      ),
    );
  }
}

void mostrarMensagem(BuildContext context, String texto, {bool erro = false}) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(
      content: Row(
        children: [
          Icon(erro ? Icons.error_outline : Icons.check_circle, color: erro ? const Color(0xFFFCA5A5) : Cores.verdeLed),
          const SizedBox(width: 10),
          Expanded(child: Text(texto)),
        ],
      ),
    ));
}
