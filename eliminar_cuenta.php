<?php
session_start();
include_once('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conectar = conn();

    // Verificar las credenciales del usuario
    $sql = "SELECT * FROM authors WHERE email = ?";
    $stmt = mysqli_prepare($conectar, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if ($password === $user['password']) {
            // Eliminar los posts del usuario
            $delete_posts = "DELETE FROM posts WHERE author_id = ?";
            $stmt_posts = mysqli_prepare($conectar, $delete_posts);
            mysqli_stmt_bind_param($stmt_posts, "i", $user['id']);
            mysqli_stmt_execute($stmt_posts);

            // Eliminar los likes del usuario
            $delete_likes = "DELETE FROM likes WHERE author_id = ?";
            $stmt_likes = mysqli_prepare($conectar, $delete_likes);
            mysqli_stmt_bind_param($stmt_likes, "i", $user['id']);
            mysqli_stmt_execute($stmt_likes);

            // Eliminar los comentarios del usuario
            $delete_comments = "DELETE FROM comments WHERE author_id = ?";
            $stmt_comments = mysqli_prepare($conectar, $delete_comments);
            mysqli_stmt_bind_param($stmt_comments, "i", $user['id']);
            mysqli_stmt_execute($stmt_comments);

            // Finalmente, eliminar la cuenta del usuario
            $delete_user = "DELETE FROM authors WHERE id = ?";
            $stmt_user = mysqli_prepare($conectar, $delete_user);
            mysqli_stmt_bind_param($stmt_user, "i", $user['id']);

            if (mysqli_stmt_execute($stmt_user)) {
                session_destroy();
                echo json_encode(['success' => true, 'message' => 'Cuenta eliminada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la cuenta']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }

    mysqli_close($conectar);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
