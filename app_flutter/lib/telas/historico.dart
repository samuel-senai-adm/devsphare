import 'package:flutter/material.dart';

import '../estado.dart';
import '../modelos.dart';
import '../tema.dart';
import '../widgets.dart';

class TelaHistorico extends StatefulWidget {
  const TelaHistorico({super.key});

  @override
  State<TelaHistorico> createState() => _TelaHistoricoState();
}

class _TelaHistoricoState extends State<TelaHistorico> {
  String _filtro = 'todas'; // todas | ativa | finalizada | cancelada

  @override
  Widget build(BuildContext context) {
    final estado = Escopo.de(context);
    final todas = estado.historico;
    final lista = _filtro == 'todas' ? todas : todas.where((r) => r.statusCodigo == _filtro).toList();

    int contar(String s) => todas.where((r) => r.statusCodigo == s).length;

    return RefreshIndicator(
      color: Cores.vermelho,
      onRefresh: estado.carregar,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
        children: [
          const Text('Histórico', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
          const SizedBox(height: 2),
          const Text('Todas as suas reservas.', style: TextStyle(fontSize: 14, color: Cores.cinza)),
          const SizedBox(height: 14),
          if (estado.erro != null) ...[
            AvisoErro(mensagem: estado.erro!, aoTentar: estado.carregar),
            const SizedBox(height: 12),
          ],
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _chip('todas', 'Todas', todas.length),
                _chip('ativa', 'Ativas', contar('ativa')),
                _chip('finalizada', 'Concluídas', contar('finalizada')),
                _chip('cancelada', 'Canceladas', contar('cancelada')),
              ],
            ),
          ),
          const SizedBox(height: 12),
          if (!estado.carregadoUmaVez && estado.erro == null)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 60),
              child: Center(child: CircularProgressIndicator(color: Cores.vermelho)),
            )
          else if (lista.isEmpty)
            Painel(
              child: EstadoVazio(
                icone: Icons.history,
                titulo: todas.isEmpty ? 'Nenhuma reserva ainda' : 'Nada neste filtro',
                texto: todas.isEmpty ? 'Suas reservas vão aparecer aqui.' : 'Escolha outro filtro.',
              ),
            )
          else
            for (final r in lista) _ItemHistorico(reserva: r),
        ],
      ),
    );
  }

  Widget _chip(String valor, String texto, int quantidade) {
    final ativo = _filtro == valor;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text('$texto  $quantidade'),
        selected: ativo,
        showCheckmark: false,
        onSelected: (_) => setState(() => _filtro = valor),
        selectedColor: Cores.grafite,
        backgroundColor: Colors.white,
        side: BorderSide(color: ativo ? Cores.grafite : Cores.borda),
        labelStyle: TextStyle(color: ativo ? Colors.white : Cores.texto2, fontWeight: FontWeight.w600, fontSize: 13),
      ),
    );
  }
}

class _ItemHistorico extends StatelessWidget {
  final Reserva reserva;

  const _ItemHistorico({required this.reserva});

  @override
  Widget build(BuildContext context) {
    final r = reserva;
    final ativa = r.ativa;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: ativa ? Cores.vermelhoSuave : Cores.borda, width: ativa ? 2 : 1),
      ),
      child: Row(
        children: [
          Container(
            width: 46,
            height: 46,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: ativa ? Cores.vermelho : Cores.fundo,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(r.numero, style: estiloNumero(16, cor: ativa ? Colors.white : Cores.texto2)),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(r.armario, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700)),
                Text('${formatarData(r.data)} · ${r.inicio} às ${r.fim}',
                    style: const TextStyle(fontSize: 13, color: Cores.cinza)),
              ],
            ),
          ),
          Selo.reserva(r),
        ],
      ),
    );
  }
}
