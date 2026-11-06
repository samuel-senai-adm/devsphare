import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../api.dart';
import '../tema.dart';
import 'principal.dart';

/// Entrar ou criar conta.
class TelaLogin extends StatefulWidget {
  final String? aviso;

  const TelaLogin({super.key, this.aviso});

  @override
  State<TelaLogin> createState() => _TelaLoginState();
}

class _TelaLoginState extends State<TelaLogin> {
  final _form = GlobalKey<FormState>();
  final _nome = TextEditingController();
  final _email = TextEditingController();
  final _senha = TextEditingController();
  final _cpf = TextEditingController();

  bool _criandoConta = false;
  bool _enviando = false;
  bool _verSenha = false;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _erro = widget.aviso;
  }

  @override
  void dispose() {
    _nome.dispose();
    _email.dispose();
    _senha.dispose();
    _cpf.dispose();
    super.dispose();
  }

  Future<void> _enviar() async {
    FocusScope.of(context).unfocus();
    if (!_form.currentState!.validate()) return;

    setState(() {
      _enviando = true;
      _erro = null;
    });

    try {
      if (_criandoConta) {
        await Api.instancia.cadastrar(
          nome: _nome.text,
          email: _email.text,
          senha: _senha.text,
          cpf: _cpf.text.replaceAll(RegExp(r'\D'), ''),
        );
      } else {
        await Api.instancia.entrar(_email.text, _senha.text);
      }
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const TelaPrincipal()));
    } on ApiException catch (e) {
      setState(() => _erro = e.mensagem);
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  void _alternarModo() {
    setState(() {
      _criandoConta = !_criandoConta;
      _erro = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Cores.grafite,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const _Marca(),
                  const SizedBox(height: 28),
                  Container(
                    padding: const EdgeInsets.fromLTRB(22, 24, 22, 22),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(22),
                    ),
                    child: Form(
                      key: _form,
                      child: AutofillGroup(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              _criandoConta ? 'Criar conta' : 'Entrar',
                              style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              _criandoConta
                                  ? 'Cadastre-se para reservar um armário.'
                                  : 'Use o mesmo e-mail e senha do site.',
                              style: const TextStyle(fontSize: 14, color: Cores.cinza),
                            ),
                            const SizedBox(height: 22),
                            if (_criandoConta) ...[
                              TextFormField(
                                controller: _nome,
                                textCapitalization: TextCapitalization.words,
                                textInputAction: TextInputAction.next,
                                autofillHints: const [AutofillHints.name],
                                decoration: const InputDecoration(
                                  labelText: 'Nome completo',
                                  prefixIcon: Icon(Icons.person_outline),
                                ),
                                validator: (v) =>
                                    (v ?? '').trim().length < 3 ? 'Informe seu nome completo.' : null,
                              ),
                              const SizedBox(height: 14),
                            ],
                            TextFormField(
                              controller: _email,
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                              autofillHints: const [AutofillHints.email],
                              decoration: const InputDecoration(
                                labelText: 'E-mail',
                                prefixIcon: Icon(Icons.mail_outline),
                              ),
                              validator: (v) {
                                final t = (v ?? '').trim();
                                if (t.isEmpty) return 'Informe seu e-mail.';
                                if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(t)) {
                                  return 'E-mail inválido.';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),
                            TextFormField(
                              controller: _senha,
                              obscureText: !_verSenha,
                              textInputAction: _criandoConta ? TextInputAction.next : TextInputAction.done,
                              autofillHints: [_criandoConta ? AutofillHints.newPassword : AutofillHints.password],
                              onFieldSubmitted: (_) => _criandoConta ? null : _enviar(),
                              decoration: InputDecoration(
                                labelText: 'Senha',
                                prefixIcon: const Icon(Icons.lock_outline),
                                suffixIcon: IconButton(
                                  tooltip: _verSenha ? 'Esconder senha' : 'Mostrar senha',
                                  icon: Icon(_verSenha ? Icons.visibility_off : Icons.visibility),
                                  onPressed: () => setState(() => _verSenha = !_verSenha),
                                ),
                              ),
                              validator: (v) {
                                if ((v ?? '').isEmpty) return 'Informe sua senha.';
                                if (_criandoConta && v!.length < 6) return 'Use pelo menos 6 caracteres.';
                                return null;
                              },
                            ),
                            if (_criandoConta) ...[
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _cpf,
                                keyboardType: TextInputType.number,
                                textInputAction: TextInputAction.done,
                                inputFormatters: [FilteringTextInputFormatter.digitsOnly, LengthLimitingTextInputFormatter(11)],
                                onFieldSubmitted: (_) => _enviar(),
                                decoration: const InputDecoration(
                                  labelText: 'CPF (só números)',
                                  prefixIcon: Icon(Icons.badge_outlined),
                                ),
                                validator: (v) => (v ?? '').length != 11 ? 'O CPF tem 11 números.' : null,
                              ),
                            ],
                            if (_erro != null) ...[
                              const SizedBox(height: 16),
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Cores.vermelhoSuave,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.error_outline, color: Cores.vermelhoEscuro, size: 20),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(_erro!,
                                          style: const TextStyle(fontSize: 13.5, color: Cores.vermelhoEscuro)),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                            const SizedBox(height: 22),
                            FilledButton(
                              onPressed: _enviando ? null : _enviar,
                              child: _enviando
                                  ? const SizedBox(
                                      width: 22,
                                      height: 22,
                                      child: CircularProgressIndicator(strokeWidth: 2.5, color: Cores.vermelho),
                                    )
                                  : Text(_criandoConta ? 'Criar conta' : 'Entrar'),
                            ),
                            const SizedBox(height: 8),
                            TextButton(
                              onPressed: _enviando ? null : _alternarModo,
                              child: Text(_criandoConta ? 'Já tenho conta. Entrar' : 'Não tem conta? Criar conta'),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Logo: cadeado vermelho + nome, como no site.
class _Marca extends StatelessWidget {
  const _Marca();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 68,
          height: 68,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Cores.vermelho, Cores.vermelhoProfundo],
            ),
            boxShadow: const [BoxShadow(color: Color(0x66E11D2E), blurRadius: 24, offset: Offset(0, 8))],
          ),
          child: const Icon(Icons.lock, color: Colors.white, size: 32),
        ),
        const SizedBox(height: 14),
        const Text('SmartLocker',
            style: TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.w700)),
        const SizedBox(height: 2),
        const Text('Seu armário, reservado em segundos',
            style: TextStyle(color: Cores.cinza2, fontSize: 14)),
      ],
    );
  }
}
