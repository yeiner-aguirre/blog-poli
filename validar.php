<?php
session_start();
include_once('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conectar = conn();

    $sql = "SELECT * FROM authors WHERE name = ?";
    $stmt = mysqli_prepare($conectar, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verificar si el usuario está bloqueado
        if ($row['is_locked']) {
            $current_time = new DateTime();
            $last_failed_attempt = new DateTime($row['last_failed_attempt']);
            $interval = $current_time->diff($last_failed_attempt);

            if ($interval->i < 2) { // Si han pasado menos de 2 minutos
                $remaining_time = 2 - $interval->i;
                $remaining_seconds = 120 - ($interval->i * 60 + $interval->s); // Calcular segundos restantes
                $_SESSION['login_error'] = "Tu cuenta está bloqueada. Intenta nuevamente en $remaining_time minutos y $remaining_seconds segundos.";
                header("Location: index.php");
                exit();
            } else {
                // Reiniciar el estado de bloqueo
                $sql_reset = "UPDATE authors SET is_locked = FALSE, failed_attempts = 0, last_failed_attempt = NULL WHERE id = ?";
                $reset_stmt = mysqli_prepare($conectar, $sql_reset);
                mysqli_stmt_bind_param($reset_stmt, "i", $row['id']);
                mysqli_stmt_execute($reset_stmt);
            }
        }

        // Verificar la contraseña
        if ($password === $row['password']) {
            // Reiniciar el contador
            $sql_update = "UPDATE authors SET failed_attempts = 0, last_failed_attempt = NULL WHERE id = ?";
            $update_stmt = mysqli_prepare($conectar, $sql_update);
            mysqli_stmt_bind_param($update_stmt, "i", $row['id']);
            mysqli_stmt_execute($update_stmt);

            $_SESSION['username'] = $row['name'];
            $_SESSION['user_id'] = $row['id'];
            header("Location: inicio.php");
            exit();
        } else {
            // Incremento contador
            $sql_update = "UPDATE authors SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() WHERE id = ?";
            $update_stmt = mysqli_prepare($conectar, $sql_update);
            mysqli_stmt_bind_param($update_stmt, "i", $row['id']);
            mysqli_stmt_execute($update_stmt);

            // Bloquear usuario
            if ($row['failed_attempts'] + 1 >= 3) {
                $sql_lock = "UPDATE authors SET is_locked = TRUE WHERE id = ?";
                $lock_stmt = mysqli_prepare($conectar, $sql_lock);
                mysqli_stmt_bind_param($lock_stmt, "i", $row['id']);
                mysqli_stmt_execute($lock_stmt);
                $_SESSION['login_error'] = "Tu cuenta ha sido bloqueada. Contacta al administrador.";
            } else {
                $_SESSION['login_error'] = "Contraseña incorrecta. Intentos fallidos: " . ($row['failed_attempts'] + 1);
            }
        }
    } else {
        $_SESSION['login_error'] = "Usuario no encontrado.";
    }

    mysqli_close($conectar);
    header("Location: index.php");
    exit();
}
