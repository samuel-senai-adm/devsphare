import 'package:flutter/material.dart';

import '../api.dart';
import '../estado.dart';
import '../modelos.dart';
import '../tema.dart';
import '../widgets.dart';
import 'principal.dart';

class TelaArmarios extends StatefulWidget {
  final void Function(int aba) irPara;

  const TelaArmarios({super.key, required this.irPara});

  @override
  State<TelaArmarios> createState() => _TelaArmariosState();
}

class _TelaArmariosState extends State<TelaArmarios> {
  String _filtro = 'todos'; // todos | livre | ocupado

  bool _passaFiltro(Armario a) {
    if (_filtro == 'todos') return true;
    if (_filtro == 'livre') return a.livre;
    return !a.livre;
  }

  /// Armário RFID só pode ser reservado por quem tem chaveiro vinculado.
  bool _precisaChaveiro(AppEstado estado, Armario a) => a.usaRfid && !estado.temChaveiro;

  void _tocar(AppEstado estado, Armario a) {
    if (a.minha) {
      widget.irPara(a.usaRfid ? Aba.inicio : Aba.qrcode);
      return;
    }
    if (!a.livre) return;
    if (_precisaChaveiro(estado, a)) {
      mostrarMensagem(context, '${a.nome} abre só com chaveiro RFID. Peça um chaveiro ao administrador.', erro: true);
      return;
    }
    if (estado.reserva != null) {
      mostrarMensagem(context, 'Você já possui uma reserva ativa. Cancele ou aguarde ela terminar.', erro: true);
      return;
    }
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (_) => Escopo(
        estado: estado,
        child: _FolhaReserva(armario: a, aoConcluir: () => widget.irPara(a.usaRfid ? Aba.inicio : Aba.qrcode)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final estado = Escopo.de(context);
    final lista = estado.armarios.where(_passaFiltro).toList();
    final livres = estado.armarios.where((a) => a.livre).length;
    final ocupados = estado.armarios.length - livres;

    return RefreshIndicator(
      color: Cores.vermelho,
      onRefresh: estado.carregar,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                const Text('Armários', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(
                  estado.reserva == null
                      ? 'Toque em um armário disponível para reservar.'
                      : 'Você já tem uma reserva ativa no ${estado.reserva!.armario}.',
                  style: const TextStyle(fontSize: 14, color: Cores.cinza),
                ),
                const SizedBox(height: 14),
                if (estado.erro != null) ...[
                  AvisoErro(mensagem: estado.erro!, aoTentar: estado.carregar),
                  const SizedBox(height: 12),
                ],
                Wrap(
                  spacing: 8,
                  children: [
                    _chip('todos', 'Todos', estado.armarios.length),
                    _chip('livre', 'Disponíveis', livres),
                    _chip('ocupado', 'Indisponíveis', ocupados),
                  ],
                ),
                const SizedBox(height: 6),
              ]),
            ),
          ),
          if (!estado.carregadoUmaVez && estado.erro == null)
            const SliverFillRemaining(
              hasScrollBody: false,
              child: Center(child: CircularProgressIndicator(color: Cores.vermelho)),
            )
          else if (lista.isEmpty)
            SliverFillRemaining(
              hasScrollBody: false,
              child: EstadoVazio(
                icone: Icons.search_off,
                titulo: 'Nenhum armário neste filtro',
                texto: 'Escolha outro filtro para ver os demais armários.',
                rotuloAcao: 'Ver todos',
                acao: () => setState(() => _filtro = 'todos'),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 28),
              sliver: SliverGrid(
                gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                  maxCrossAxisExtent: 230,
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  mainAxisExtent: 268,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, i) {
                    final a = lista[i];
                    final semChaveiro = a.livre && _precisaChaveiro(estado, a);
                    final rotulo = a.minha
                        ? (a.usaRfid ? 'Ver reserva' : 'Ver QR Code')
                        : semChaveiro
                            ? 'Precisa de chaveiro'
                            : (a.livre ? 'Reservar' : 'Indisponível');
                    return PortaArmario(
                      armario: a,
                      rotuloBotao: rotulo,
                      aoTocar: ((a.livre && !semChaveiro) || a.minha) ? () => _tocar(estado, a) : null,
                    );
                  },
                  childCount: lista.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _chip(String valor, String texto, int quantidade) {
    final ativo = _filtro == valor;
    return ChoiceChip(
      label: Text('$texto  $quantidade'),
      selected: ativo,
      showCheckmark: false,
      onSelected: (_) => setState(() => _filtro = valor),
      selectedColor: Cores.grafite,
      backgroundColor: Colors.white,
      side: BorderSide(color: ativo ? Cores.grafite : Cores.borda),
      labelStyle: TextStyle(
        color: ativo ? Colors.white : Cores.texto2,
        fontWeight: FontWeight.w600,
        fontSize: 13,
      ),
    );
  }
}

/* ===================== FOLHA DE RESERVA ===================== */

class _FolhaReserva extends StatefulWidget {
  final Armario armario;
  final VoidCallback aoConcluir;

  const _FolhaReserva({required this.armario, required this.aoConcluir});

  @override
  State<_FolhaReserva> createState() => _FolhaReservaState();
}

class _FolhaReservaState extends State<_FolhaReserva> {
  late DateTime _data;
  TimeOfDay? _inicio;
  TimeOfDay? _fim;
  int? _duracaoEscolhida;
  bool _enviando = false;
  String? _erro;

  @override
  void initState() {
    super.initState();
    final hoje = DateTime.now();
    _data = DateTime(hoje.year, hoje.month, hoje.day);
  }

  int _minutos(TimeOfDay t) => t.hour * 60 + t.minute;

  String _hora(TimeOfDay? t) => t == null ? '--:--' : '${doisDigitos(t.hour)}:${doisDigitos(t.minute)}';

  DateTime _combinar(TimeOfDay t) => DateTime(_data.year, _data.month, _data.day, t.hour, t.minute);

  Future<void> _escolherData() async {
    final hoje = DateTime.now();
    final escolhida = await showDatePicker(
      context: context,
      initialDate: _data,
      firstDate: DateTime(hoje.year, hoje.month, hoje.day),
      lastDate: hoje.add(const Duration(days: 30)),
      helpText: 'Data da reserva',
    );
    if (escolhida != null) {
      setState(() {
        _data = escolhida;
        _erro = null;
      });
    }
  }

  Future<void> _escolherHora(bool inicio) async {
    final atual = inicio ? _inicio : _fim;
    final escolhida = await showTimePicker(
      context: context,
      initialTime: atual ?? TimeOfDay.fromDateTime(DateTime.now().add(const Duration(minutes: 5))),
      helpText: inicio ? 'Horário de início' : 'Horário de término',
      builder: (context, child) => MediaQuery(
        data: MediaQuery.of(context).copyWith(alwaysUse24HourFormat: true),
        child: child!,
      ),
    );
    if (escolhida != null) {
      setState(() {
        if (inicio) {
          _inicio = escolhida;
        } else {
          _fim = escolhida;
        }
        _duracaoEscolhida = null;
        _erro = null;
      });
    }
  }

  void _duracaoRapida(int minutos) {
    var inicio = _inicio;
    if (inicio == null) {
      final daqui = DateTime.now().add(const Duration(minutes: 5));
      inicio = TimeOfDay(hour: daqui.hour, minute: daqui.minute);
    }
    final total = _minutos(inicio) + minutos;
    if (total >= 24 * 60) {
      setState(() => _erro = 'Essa duração passa da meia-noite. Escolha um término no mesmo dia.');
      return;
    }
    setState(() {
      _inicio = inicio;
      _fim = TimeOfDay(hour: total ~/ 60, minute: total % 60);
      _duracaoEscolhida = minutos;
      _erro = null;
    });
  }

  String? _validar() {
    if (_inicio == null) return 'Escolha o horário de início.';
    if (_fim == null) return 'Escolha o horário de término.';
    if (_minutos(_fim!) <= _minutos(_inicio!)) return 'O término precisa ser depois do início.';
    if (_combinar(_inicio!).isBefore(DateTime.now())) return 'O início precisa ser um horário futuro.';
    if (_minutos(_fim!) - _minutos(_inicio!) < 15) return 'A reserva precisa ter pelo menos 15 minutos.';
    return null;
  }

  Future<void> _confirmar() async {
    final erro = _validar();
    if (erro != null) {
      setState(() => _erro = erro);
      return;
    }

    setState(() {
      _enviando = true;
      _erro = null;
    });

    final estado = Escopo.ler(context);
    try {
      final reserva = await estado.reservar(
        armario: widget.armario,
        data: _data,
        inicio: _inicio!,
        fim: _fim!,
      );
      if (!mounted) return;
      final mensageiro = ScaffoldMessenger.of(context);
      Navigator.of(context).pop();
      widget.aoConcluir();
      mensageiro.showSnackBar(SnackBar(content: Text('Reserva do ${reserva.armario} confirmada.')));
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _enviando = false;
        _erro = e.mensagem;
      });
      estado.carregar(); // o armário pode ter sido reservado por outra pessoa
    }
  }

  @override
  Widget build(BuildContext context) {
    final a = widget.armario;
    final temIntervalo = _inicio != null && _fim != null && _minutos(_fim!) > _minutos(_inicio!);

    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: Cores.borda, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 18),
            const Text('Fazer reserva', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
            const Text('Escolha a data e o horário de uso.', style: TextStyle(fontSize: 14, color: Cores.cinza)),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Cores.fundo, borderRadius: BorderRadius.circular(14)),
              child: Row(
                children: [
                  Container(
                    width: 46,
                    height: 46,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(color: Cores.vermelho, borderRadius: BorderRadius.circular(12)),
                    child: Text(a.numero, style: estiloNumero(17, cor: Colors.white)),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(a.nome, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                        Text(a.localizacao, style: const TextStyle(color: Cores.cinza, fontSize: 13)),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Selo.armario('livre'),
                      const SizedBox(height: 6),
                      Selo.metodo(a.metodoAcesso),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            // Como o armário abre: aparece antes de confirmar a reserva
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Cores.borda),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(a.usaRfid ? Icons.nfc : Icons.qr_code_2, size: 20, color: Cores.cinza),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text.rich(
                      TextSpan(
                        style: const TextStyle(fontSize: 13, color: Cores.texto2, height: 1.4),
                        children: a.usaRfid
                            ? const [
                                TextSpan(
                                    text: 'Abre com o seu chaveiro RFID. ',
                                    style: TextStyle(fontWeight: FontWeight.w700, color: Cores.texto)),
                                TextSpan(text: 'Na hora de usar, encoste o chaveiro no leitor do armário.'),
                              ]
                            : const [
                                TextSpan(
                                    text: 'Abre com QR Code. ',
                                    style: TextStyle(fontWeight: FontWeight.w700, color: Cores.texto)),
                                TextSpan(text: 'Depois de reservar, o código aparece em "Meu QR Code".'),
                              ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            _Campo(
              rotulo: 'Data',
              valor: formatarData('${_data.year}-${doisDigitos(_data.month)}-${doisDigitos(_data.day)}'),
              icone: Icons.calendar_today,
              aoTocar: _enviando ? null : _escolherData,
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: _Campo(
                    rotulo: 'Início',
                    valor: _hora(_inicio),
                    icone: Icons.schedule,
                    aoTocar: _enviando ? null : () => _escolherHora(true),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _Campo(
                    rotulo: 'Término',
                    valor: _hora(_fim),
                    icone: Icons.schedule,
                    aoTocar: _enviando ? null : () => _escolherHora(false),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            const Text('Duração rápida', style: TextStyle(fontSize: 13, color: Cores.cinza)),
            const SizedBox(height: 6),
            Wrap(
              spacing: 8,
              children: [
                for (final m in const [60, 120, 240])
                  ChoiceChip(
                    label: Text(textoDuracao(m)),
                    selected: _duracaoEscolhida == m,
                    showCheckmark: false,
                    selectedColor: Cores.vermelhoSuave,
                    side: BorderSide(color: _duracaoEscolhida == m ? Cores.vermelho : Cores.borda),
                    labelStyle: TextStyle(
                      color: _duracaoEscolhida == m ? Cores.vermelhoEscuro : Cores.texto2,
                      fontWeight: FontWeight.w600,
                    ),
                    onSelected: _enviando ? null : (_) => _duracaoRapida(m),
                  ),
              ],
            ),
            if (temIntervalo) ...[
              const SizedBox(height: 14),
              Row(
                children: [
                  const Icon(Icons.hourglass_bottom, size: 18, color: Cores.cinza),
                  const SizedBox(width: 6),
                  Text(
                    'Das ${_hora(_inicio)} às ${_hora(_fim)} · ${textoDuracao(_minutos(_fim!) - _minutos(_inicio!))}',
                    style: const TextStyle(fontSize: 13.5, color: Cores.texto2),
                  ),
                ],
              ),
            ],
            if (_erro != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(color: Cores.vermelhoSuave, borderRadius: BorderRadius.circular(12)),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: Cores.vermelhoEscuro, size: 20),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(_erro!, style: const TextStyle(color: Cores.vermelhoEscuro, fontSize: 13.5)),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 20),
            FilledButton.icon(
              onPressed: _enviando ? null : _confirmar,
              icon: _enviando
                  ? const SizedBox(
                      width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Cores.vermelho))
                  : const Icon(Icons.check),
              label: Text(_enviando ? 'Reservando…' : 'Confirmar reserva'),
            ),
          ],
        ),
      ),
    );
  }
}

class _Campo extends StatelessWidget {
  final String rotulo;
  final String valor;
  final IconData icone;
  final VoidCallback? aoTocar;

  const _Campo({required this.rotulo, required this.valor, required this.icone, this.aoTocar});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      child: InkWell(
        onTap: aoTocar,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Cores.borda),
          ),
          child: Row(
            children: [
              Icon(icone, size: 18, color: Cores.cinza),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(rotulo, style: const TextStyle(fontSize: 12, color: Cores.cinza)),
                    Text(valor, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
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
