import 'dart:async';

import 'package:flutter/material.dart';

import '../estado.dart';
import '../modelos.dart';
import '../tema.dart';
import '../widgets.dart';
import 'principal.dart';

class TelaInicio extends StatelessWidget {
  final void Function(int aba) irPara;

  const TelaInicio({super.key, required this.irPara});

  @override
  Widget build(BuildContext context) {
    final estado = Escopo.de(context);
    final agora = DateTime.now();
    final nome = estado.usuario?.primeiroNome ?? '';

    return RefreshIndicator(
      color: Cores.vermelho,
      onRefresh: estado.carregar,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          Text(dataPorExtenso(agora), style: const TextStyle(fontSize: 13, color: Cores.cinza)),
          const SizedBox(height: 2),
          Text(
            nome.isEmpty ? saudacao(agora) : '${saudacao(agora)}, $nome',
            style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w700),
          ),
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
          else ...[
            _CartaoPrincipal(estado: estado, irPara: irPara),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _Numero(
                    icone: Icons.lock_open,
                    corIcone: Cores.verde,
                    fundoIcone: Cores.verdeSuave,
                    valor: '${estado.disponiveis}',
                    rotulo: 'Armários livres',
                    aoTocar: () => irPara(Aba.armarios),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _Numero(
                    icone: Icons.history,
                    corIcone: Cores.texto2,
                    fundoIcone: Cores.cinzaClaro,
                    valor: '${estado.numeros.total}',
                    rotulo: 'Reservas feitas',
                    aoTocar: () => irPara(Aba.historico),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            _Atividade(estado: estado),
          ],
        ],
      ),
    );
  }
}

/* ===================== CARTÃO PRINCIPAL ===================== */

/// Cartão grafite com a situação do usuário e o mapa das portas.
class _CartaoPrincipal extends StatelessWidget {
  final AppEstado estado;
  final void Function(int aba) irPara;

  const _CartaoPrincipal({required this.estado, required this.irPara});

  @override
  Widget build(BuildContext context) {
    final reserva = estado.reserva;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(22),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Cores.grafite, Cores.grafite2, Color(0xFF3A0C12)],
          stops: [0, 0.6, 1],
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _MapaPortas(armarios: estado.armarios),
          const SizedBox(height: 20),
          if (reserva == null) ...[
            const Row(children: [
              Led(cor: Cores.cinza2, tamanho: 7),
              SizedBox(width: 8),
              Text('Nenhuma reserva ativa', style: TextStyle(color: Cores.cinza2, fontSize: 13)),
            ]),
            const SizedBox(height: 8),
            const Text('Escolha um armário livre e reserve em poucos segundos.',
                style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w600, height: 1.35)),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: () => irPara(Aba.armarios),
                icon: const Icon(Icons.event_available),
                label: const Text('Reservar armário'),
              ),
            ),
          ] else
            _ReservaAtiva(reserva: reserva, irPara: irPara),
        ],
      ),
    );
  }
}

/// Fileira de portas com LED: verde livre, branca a sua, apagada ocupada.
class _MapaPortas extends StatelessWidget {
  final List<Armario> armarios;

  const _MapaPortas({required this.armarios});

  @override
  Widget build(BuildContext context) {
    if (armarios.isEmpty) return const SizedBox.shrink();

    return Semantics(
      label: 'Mapa dos armários',
      child: Row(
        children: [
          for (final a in armarios.take(8))
            Expanded(
              child: Container(
                height: 58,
                margin: const EdgeInsets.symmetric(horizontal: 3),
                padding: const EdgeInsets.fromLTRB(6, 6, 6, 8),
                decoration: BoxDecoration(
                  color: a.minha ? Cores.vermelho : Cores.grafite3,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: a.minha ? Cores.vermelho : const Color(0xFF353A44)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(a.numero,
                        textAlign: TextAlign.center,
                        style: estiloNumero(11, cor: a.minha ? Colors.white : Cores.cinza2)),
                    const Spacer(),
                    Align(
                      alignment: Alignment.centerRight,
                      child: Container(
                        width: 3,
                        height: 14,
                        decoration: BoxDecoration(
                          color: corLed(a.status),
                          borderRadius: BorderRadius.circular(2),
                          boxShadow: a.livre ? const [BoxShadow(color: Cores.verdeLed, blurRadius: 6)] : null,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

/// Reserva ativa com contagem regressiva (atualiza sozinha).
class _ReservaAtiva extends StatefulWidget {
  final Reserva reserva;
  final void Function(int aba) irPara;

  const _ReservaAtiva({required this.reserva, required this.irPara});

  @override
  State<_ReservaAtiva> createState() => _ReservaAtivaState();
}

class _ReservaAtivaState extends State<_ReservaAtiva> {
  Timer? _relogio;

  @override
  void initState() {
    super.initState();
    _relogio = Timer.periodic(const Duration(seconds: 20), (_) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _relogio?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final r = widget.reserva;
    final agora = DateTime.now();
    final emAndamento = !agora.isBefore(r.dataInicio);
    final total = r.dataFim.difference(r.dataInicio).inSeconds;
    final passou = agora.difference(r.dataInicio).inSeconds;
    final progresso = emAndamento && total > 0 ? (passou / total).clamp(0.0, 1.0) : 0.0;
    final contagem = emAndamento
        ? 'Termina em ${textoTempo(r.dataFim.difference(agora))}'
        : 'Começa em ${textoTempo(r.dataInicio.difference(agora))}';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(children: [
          const Led(cor: Cores.vermelho, tamanho: 7),
          const SizedBox(width: 8),
          Text(emAndamento ? 'Reserva em andamento' : 'Reserva agendada',
              style: const TextStyle(color: Cores.brancoMeio, fontSize: 13)),
        ]),
        const SizedBox(height: 10),
        Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Container(
              width: 62,
              height: 62,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: Cores.vermelho,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Text(r.numero, style: estiloNumero(24, cor: Colors.white)),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(r.armario,
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w700)),
                  Text('${r.localizacao} · ${formatarData(r.data)}, ${r.inicio} às ${r.fim}',
                      style: const TextStyle(color: Cores.cinza2, fontSize: 13)),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: Text(contagem, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w500)),
            ),
            Text('${(progresso * 100).round()}%', style: estiloNumero(12, cor: Cores.cinza2)),
          ],
        ),
        const SizedBox(height: 8),
        ClipRRect(
          borderRadius: BorderRadius.circular(99),
          child: LinearProgressIndicator(
            value: progresso,
            minHeight: 6,
            backgroundColor: Cores.brancoTransparente,
            color: Cores.vermelho,
          ),
        ),
        const SizedBox(height: 18),
        SizedBox(
          width: double.infinity,
          child: FilledButton.icon(
            onPressed: () => widget.irPara(Aba.qrcode),
            style: FilledButton.styleFrom(backgroundColor: Colors.white, foregroundColor: Cores.grafite),
            icon: const Icon(Icons.qr_code_2),
            label: const Text('Mostrar QR Code'),
          ),
        ),
      ],
    );
  }
}

/* ===================== NÚMEROS ===================== */

class _Numero extends StatelessWidget {
  final IconData icone;
  final Color corIcone;
  final Color fundoIcone;
  final String valor;
  final String rotulo;
  final VoidCallback aoTocar;

  const _Numero({
    required this.icone,
    required this.corIcone,
    required this.fundoIcone,
    required this.valor,
    required this.rotulo,
    required this.aoTocar,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: aoTocar,
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: Cores.borda),
          ),
          child: Row(
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(color: fundoIcone, borderRadius: BorderRadius.circular(12)),
                child: Icon(icone, color: corIcone, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(valor, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700, height: 1.1)),
                    Text(rotulo,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 12, color: Cores.cinza)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/* ===================== ATIVIDADE ===================== */

class _Evento {
  final DateTime quando;
  final IconData icone;
  final Color cor;
  final Color fundo;
  final String titulo;
  final String detalhe;

  _Evento(this.quando, this.icone, this.cor, this.fundo, this.titulo, this.detalhe);
}

/// Reservas e aberturas do armário (QR / RFID) numa linha do tempo.
class _Atividade extends StatelessWidget {
  final AppEstado estado;

  const _Atividade({required this.estado});

  static const _metodos = {'rfid': 'chaveiro RFID', 'qrcode': 'QR Code', 'app': 'aplicativo', 'senha': 'senha'};

  @override
  Widget build(BuildContext context) {
    final eventos = <_Evento>[
      for (final r in estado.historico)
        _Evento(
          r.criadaEm ?? r.dataInicio,
          r.statusCodigo == 'cancelada'
              ? Icons.event_busy
              : (r.statusCodigo == 'finalizada' ? Icons.check_circle : Icons.lock),
          r.statusCodigo == 'finalizada'
              ? Cores.verde
              : (r.statusCodigo == 'cancelada' ? Cores.cinza : Cores.vermelho),
          r.statusCodigo == 'finalizada'
              ? Cores.verdeSuave
              : (r.statusCodigo == 'cancelada' ? Cores.cinzaClaro : Cores.vermelhoSuave),
          'Reserva ${r.status.toLowerCase()} · ${r.armario}',
          '${formatarData(r.data)}, ${r.inicio} às ${r.fim}',
        ),
      for (final a in estado.acessos)
        _Evento(
          a.dataHora,
          a.liberado ? Icons.meeting_room : Icons.block,
          a.liberado ? Cores.verde : Cores.ambar,
          a.liberado ? Cores.verdeSuave : Cores.ambarSuave,
          a.liberado
              ? '${a.tipo == 'saida' ? 'Retirada no' : 'Porta aberta no'} ${a.armario}'
              : 'Acesso negado no ${a.armario}',
          '${formatarDataHora(a.dataHora)}, pelo ${_metodos[a.metodo] ?? a.metodo}',
        ),
    ]..sort((x, y) => y.quando.compareTo(x.quando));

    return Painel(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const TituloSecao(titulo: 'Atividade recente', subtitulo: 'Suas reservas e aberturas do armário'),
          const SizedBox(height: 12),
          if (eventos.isEmpty)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(
                child: Text('Nenhuma atividade ainda.', style: TextStyle(color: Cores.cinza, fontSize: 13)),
              ),
            ),
          for (final e in eventos.take(5))
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 7),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(color: e.fundo, borderRadius: BorderRadius.circular(10)),
                    child: Icon(e.icone, color: e.cor, size: 18),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(e.titulo, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                        Text(e.detalhe, style: const TextStyle(fontSize: 12, color: Cores.cinza)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
