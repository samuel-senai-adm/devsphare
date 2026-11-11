/// Modelos que espelham as respostas JSON da API (sistema/api/*.php).

class Usuario {
  final int id;
  final String nome;
  final String email;
  final String? telefone;
  final String perfil;
  final bool chaveiroRfid;
  final String? clienteDesde;

  Usuario({
    required this.id,
    required this.nome,
    required this.email,
    this.telefone,
    required this.perfil,
    required this.chaveiroRfid,
    this.clienteDesde,
  });

  factory Usuario.deJson(Map<String, dynamic> j) => Usuario(
        id: (j['id'] as num).toInt(),
        nome: (j['nome'] ?? '').toString(),
        email: (j['email'] ?? '').toString(),
        telefone: j['telefone']?.toString(),
        perfil: (j['perfil'] ?? 'usuario').toString(),
        chaveiroRfid: j['chaveiro_rfid'] == true,
        clienteDesde: j['cliente_desde']?.toString(),
      );

  String get primeiroNome {
    final partes = nome.trim().split(RegExp(r'\s+'));
    return partes.isEmpty || partes.first.isEmpty ? 'Usuário' : partes.first;
  }

  String get inicial => nome.trim().isEmpty ? 'U' : nome.trim()[0].toUpperCase();

  String get tipoConta => perfil == 'admin' ? 'Administrador' : 'Cliente';
}

/// status: livre | ocupado | manutencao | minha
/// metodoAcesso: qrcode | rfid  (como o armário abre, definido pelo administrador)
class Armario {
  final int id;
  final String numero;
  final String nome;
  final String localizacao;
  final String status;
  final String metodoAcesso;

  Armario({
    required this.id,
    required this.numero,
    required this.nome,
    required this.localizacao,
    required this.status,
    this.metodoAcesso = 'qrcode',
  });

  factory Armario.deJson(Map<String, dynamic> j) => Armario(
        id: (j['id'] as num).toInt(),
        numero: (j['numero'] ?? '').toString(),
        nome: (j['nome'] ?? '').toString(),
        localizacao: (j['localizacao'] ?? '').toString(),
        status: (j['status'] ?? 'ocupado').toString(),
        metodoAcesso: j['metodo_acesso'] == 'rfid' ? 'rfid' : 'qrcode',
      );

  bool get livre => status == 'livre';
  bool get minha => status == 'minha';
  bool get usaRfid => metodoAcesso == 'rfid';
}

class Reserva {
  final int id;
  final int lockerId;
  final String numero;
  final String armario;
  final String localizacao;
  final String data; // AAAA-MM-DD
  final String inicio; // HH:MM
  final String fim; // HH:MM
  final DateTime dataInicio;
  final DateTime dataFim;
  final String status; // Ativa | Concluída | Cancelada
  final String statusCodigo; // ativa | finalizada | cancelada
  final DateTime? criadaEm;
  final String? qr;
  final String metodoAcesso; // qrcode | rfid

  Reserva({
    required this.id,
    required this.lockerId,
    required this.numero,
    required this.armario,
    required this.localizacao,
    required this.data,
    required this.inicio,
    required this.fim,
    required this.dataInicio,
    required this.dataFim,
    required this.status,
    required this.statusCodigo,
    this.criadaEm,
    this.qr,
    this.metodoAcesso = 'qrcode',
  });

  factory Reserva.deJson(Map<String, dynamic> j) => Reserva(
        id: (j['id'] as num).toInt(),
        lockerId: (j['locker_id'] as num).toInt(),
        numero: (j['numero'] ?? '').toString(),
        armario: (j['armario'] ?? '').toString(),
        localizacao: (j['localizacao'] ?? '').toString(),
        data: (j['data'] ?? '').toString(),
        inicio: (j['inicio'] ?? '').toString(),
        fim: (j['fim'] ?? '').toString(),
        dataInicio: DateTime.parse(j['data_inicio'].toString()).toLocal(),
        dataFim: DateTime.parse(j['data_fim'].toString()).toLocal(),
        status: (j['status'] ?? '').toString(),
        statusCodigo: (j['status_codigo'] ?? '').toString(),
        criadaEm: j['criada_em'] == null ? null : DateTime.tryParse(j['criada_em'].toString())?.toLocal(),
        qr: j['qr']?.toString(),
        metodoAcesso: j['metodo_acesso'] == 'rfid' ? 'rfid' : 'qrcode',
      );

  bool get usaRfid => metodoAcesso == 'rfid';
  bool get ativa => statusCodigo == 'ativa';
  bool get emAndamento => ativa && !DateTime.now().isBefore(dataInicio);
}

class Acesso {
  final String armario;
  final String? tipo; // entrada | saida
  final String metodo; // rfid | qrcode
  final String resultado; // liberado | negado
  final DateTime dataHora;

  Acesso({
    required this.armario,
    this.tipo,
    required this.metodo,
    required this.resultado,
    required this.dataHora,
  });

  factory Acesso.deJson(Map<String, dynamic> j) => Acesso(
        armario: (j['armario'] ?? '').toString(),
        tipo: j['tipo']?.toString(),
        metodo: (j['metodo'] ?? '').toString(),
        resultado: (j['resultado'] ?? '').toString(),
        dataHora: DateTime.parse(j['data_hora'].toString()).toLocal(),
      );

  bool get liberado => resultado == 'liberado';
}

/// Números do perfil (api/perfil.php)
class NumerosPerfil {
  final int total;
  final int concluidas;
  final int canceladas;

  const NumerosPerfil({this.total = 0, this.concluidas = 0, this.canceladas = 0});

  factory NumerosPerfil.deJson(Map<String, dynamic> j) => NumerosPerfil(
        total: (j['total'] as num? ?? 0).toInt(),
        concluidas: (j['concluidas'] as num? ?? 0).toInt(),
        canceladas: (j['canceladas'] as num? ?? 0).toInt(),
      );
}
