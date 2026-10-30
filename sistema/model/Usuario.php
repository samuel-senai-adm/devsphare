<?php

class Usuario
{

    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    /* =========================
       VERIFICAR EMAIL
    ========================= */

    public function emailExiste($email)
    {

        $sql = "SELECT id FROM usuarios WHERE email = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->num_rows > 0;
    }


    /* =========================
       CADASTRO
    ========================= */

    public function cadastrar($nome, $email, $senha, $cpf)
    {

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
                (nome, email, senha, cpf)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $nome,
            $email,
            $senhaHash,
            $cpf
        );

        return $stmt->execute();
    }


    /* =========================
       LOGIN
    ========================= */

    public function login($email, $senha)
    {

        $sql = "SELECT * FROM usuarios
                WHERE email = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {

            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario['senha'])) {
                return $usuario;
            }
        }

        return false;
    }
}

?>
