import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Identidade SmartLocker: vermelho, branco e grafite (igual ao dashboard web).
class Cores {
  static const vermelho = Color(0xFFE11D2E);
  static const vermelhoEscuro = Color(0xFFC51626);
  static const vermelhoProfundo = Color(0xFF9E0F1C);
  static const vermelhoSuave = Color(0xFFFDECEE);

  static const grafite = Color(0xFF14161B);
  static const grafite2 = Color(0xFF1C1F26);
  static const grafite3 = Color(0xFF272B33);

  static const texto = Color(0xFF1A1D24);
  static const texto2 = Color(0xFF4B5260);
  static const cinza = Color(0xFF6B7280);
  static const cinza2 = Color(0xFF9AA1AB);
  static const cinzaClaro = Color(0xFFEEF0F3);
  static const borda = Color(0xFFE5E7EB);
  static const fundo = Color(0xFFF4F5F7);
  static const branco = Colors.white;

  static const verde = Color(0xFF15803D);
  static const verdeLed = Color(0xFF22C55E);
  static const verdeSuave = Color(0xFFE7F6EC);
  static const ambar = Color(0xFFB45309);
  static const ambarSuave = Color(0xFFFEF3C7);

  static const brancoTransparente = Color(0x1FFFFFFF);
  static const brancoMeio = Color(0xB3FFFFFF);
}

/// Números dos armários usam fonte mono, como na porta física e no site.
TextStyle estiloNumero(double tamanho, {Color cor = Cores.texto}) =>
    GoogleFonts.jetBrainsMono(fontSize: tamanho, fontWeight: FontWeight.w700, color: cor);

ThemeData temaSmartLocker() {
  final base = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: Cores.vermelho,
      primary: Cores.vermelho,
      onPrimary: Colors.white,
      surface: Colors.white,
      brightness: Brightness.light,
    ),
    scaffoldBackgroundColor: Cores.fundo,
  );

  final texto = GoogleFonts.poppinsTextTheme(base.textTheme).apply(
    bodyColor: Cores.texto,
    displayColor: Cores.texto,
  );

  final bordaCampo = OutlineInputBorder(
    borderRadius: BorderRadius.circular(12),
    borderSide: const BorderSide(color: Cores.borda),
  );

  return base.copyWith(
    textTheme: texto,
    appBarTheme: AppBarTheme(
      backgroundColor: Cores.fundo,
      foregroundColor: Cores.texto,
      elevation: 0,
      scrolledUnderElevation: 0,
      titleTextStyle: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.w700, color: Cores.texto),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: Cores.vermelho,
        foregroundColor: Colors.white,
        disabledBackgroundColor: Cores.cinzaClaro,
        minimumSize: const Size(0, 52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: GoogleFonts.poppins(fontSize: 15, fontWeight: FontWeight.w600),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: Cores.texto,
        minimumSize: const Size(0, 52),
        side: const BorderSide(color: Cores.borda),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: GoogleFonts.poppins(fontSize: 15, fontWeight: FontWeight.w600),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      border: bordaCampo,
      enabledBorder: bordaCampo,
      focusedBorder: bordaCampo.copyWith(borderSide: const BorderSide(color: Cores.vermelho, width: 2)),
      errorBorder: bordaCampo.copyWith(borderSide: const BorderSide(color: Cores.vermelhoEscuro)),
      labelStyle: GoogleFonts.poppins(color: Cores.cinza),
    ),
    snackBarTheme: SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      backgroundColor: Cores.grafite,
      contentTextStyle: GoogleFonts.poppins(color: Colors.white, fontSize: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),
  );
}
