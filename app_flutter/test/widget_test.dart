import 'package:flutter_test/flutter_test.dart';

import 'package:smartlocker_app/modelos.dart';
import 'package:smartlocker_app/widgets.dart';

void main() {
  test('lê uma reserva no formato da API', () {
    final r = Reserva.deJson({
      'id': 1,
      'locker_id': 5,
      'numero': '01',
      'armario': 'Armário 01',
      'localizacao': 'Bloco A',
      'data': '2026-11-04',
      'inicio': '14:00',
      'fim': '15:30',
      'data_inicio': '2026-11-04T14:00:00-03:00',
      'data_fim': '2026-11-04T15:30:00-03:00',
      'status': 'Ativa',
      'status_codigo': 'ativa',
      'criada_em': '2026-11-04T13:00:00-03:00',
      'qr': 'SMARTLOCKER:0123456789abcdef0123456789abcdef',
    });
    expect(r.ativa, isTrue);
    expect(r.dataFim.difference(r.dataInicio).inMinutes, 90);
    expect(r.qr, startsWith('SMARTLOCKER:'));
  });

  test('formata datas e durações', () {
    expect(formatarData('2026-11-04'), '04/11/2026');
    expect(textoDuracao(90), '1h 30min');
    expect(textoTempo(const Duration(minutes: 135)), '2h 15min');
  });
}
