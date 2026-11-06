# SmartLocker — aplicativo do usuário (Flutter)

App do cliente: entrar / criar conta, ver os armários, reservar, mostrar o QR Code de acesso,
cancelar, ver o histórico e o perfil. Usa a **mesma API do site** (`sistema/api`), então o que
você faz no app aparece no dashboard e vice-versa.

## 1. Criar o projeto (uma vez só)

No terminal do VS Code, numa pasta fora do site:

```
flutter create smartlocker_app
```

Depois **substitua** dentro de `smartlocker_app/`:

- a pasta `lib/` inteira pela `lib/` daqui
- o arquivo `pubspec.yaml` pelo daqui
- o arquivo `test/widget_test.dart` pelo daqui (o original procura uma classe `MyApp` que não existe mais)

E rode:

```
flutter pub get
```

## 2. Permissões do Android

Abra `android/app/src/main/AndroidManifest.xml`:

1. Logo acima de `<application`, adicione:

```xml
<uses-permission android:name="android.permission.INTERNET"/>
```

2. **Só se for testar no XAMPP (endereço `http://`)**, adicione dentro da tag `<application ...>`:

```xml
android:usesCleartextTraffic="true"
```

Fica assim: `<application android:label="smartlocker_app" android:usesCleartextTraffic="true" ...>`

## 3. Apontar para o servidor

Abra `lib/config.dart` e ajuste `apiBase`:

| Onde está o site | Valor de `apiBase` |
|---|---|
| Site no ar | `https://devsphare.com.br/sistema/api/` |
| XAMPP no seu PC | `http://IP-DO-PC/devsphare/sistema/api/` |

Para achar o IP do PC: Prompt de Comando → `ipconfig` → "Endereço IPv4". O tablet e o PC
precisam estar no **mesmo Wi-Fi**. Teste primeiro abrindo `http://IP-DO-PC/devsphare/sistema/api/armarios.php`
no navegador do tablet: deve aparecer `{"ok":false,"erro":"Sua sessão expirou..."}` — isso significa que chegou na API.

Se o Windows bloquear, libere o Apache no Firewall do Windows (rede privada).

## 4. Rodar no tablet

Tablet no cabo, com "Depuração USB" ligada (Opções do desenvolvedor):

```
flutter devices
flutter run
```

## Como o app está organizado

```
lib/
  main.dart          abre o app (login ou tela principal)
  config.dart        endereço do servidor
  api.dart           chamadas HTTP + token do login
  estado.dart        dados carregados do banco (armários, reserva, histórico)
  modelos.dart       Usuario, Armario, Reserva, Acesso
  tema.dart          cores e fontes SmartLocker
  widgets.dart       peças reutilizadas (porta do armário, selos, avisos)
  telas/
    login.dart       entrar / criar conta
    principal.dart   navegação (barra embaixo no celular, lateral no tablet)
    inicio.dart      resumo, reserva ativa com contagem, atividade
    armarios.dart    grade de armários + folha de reserva
    qrcode.dart      QR Code da reserva + cancelar
    historico.dart   lista de reservas com filtros
    perfil.dart      dados da conta e sair
```

O app atualiza sozinho a cada 30 segundos e quando você volta para ele; também dá para
puxar a tela para baixo para atualizar.
