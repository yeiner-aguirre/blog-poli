<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Incluir la conexión a la base de datos
include_once('db.php');
$conectar = conn();

// Obtener el user_id desde la tabla authors usando el username
$username = $_SESSION['username'];
$sql_user = "SELECT id FROM authors WHERE name = ?";
$stmt_user = mysqli_prepare($conectar, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
$author_id = $user_data['id'];

// Verificar si se proporcionó un ID de post
if (!isset($_GET['id'])) {
    header("Location: inicio.php");
    exit();
}

$post_id = $_GET['id'];

// Verificar si el post pertenece al usuario actual
$sql_check = "SELECT id FROM posts WHERE id = ? AND author_id = ?";
$stmt_check = mysqli_prepare($conectar, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $post_id, $author_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    header("Location: inicio.php");
    exit();
}

// Procesar la eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    // Iniciar transacción
    mysqli_begin_transaction($conectar);

    try {
        // Primero eliminar los comentarios asociados
        $sql_delete_comments = "DELETE FROM comments WHERE post_id = ?";
        $stmt_delete_comments = mysqli_prepare($conectar, $sql_delete_comments);
        mysqli_stmt_bind_param($stmt_delete_comments, "i", $post_id);
        mysqli_stmt_execute($stmt_delete_comments);

        // Luego eliminar los likes asociados
        $sql_delete_likes = "DELETE FROM likes WHERE post_id = ?";
        $stmt_delete_likes = mysqli_prepare($conectar, $sql_delete_likes);
        mysqli_stmt_bind_param($stmt_delete_likes, "i", $post_id);
        mysqli_stmt_execute($stmt_delete_likes);

        // Finalmente eliminar el post
        $sql_delete_post = "DELETE FROM posts WHERE id = ? AND author_id = ?";
        $stmt_delete_post = mysqli_prepare($conectar, $sql_delete_post);
        mysqli_stmt_bind_param($stmt_delete_post, "ii", $post_id, $author_id);
        mysqli_stmt_execute($stmt_delete_post);

        mysqli_commit($conectar);

        $_SESSION['message'] = "Post eliminado exitosamente.";
        $_SESSION['message_type'] = "success";

        header("Location: inicio.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conectar);

        $_SESSION['message'] = "Error al eliminar el post: " . $e->getMessage();
        $_SESSION['message_type'] = "error";

        header("Location: inicio.php");
        exit();
    }
}

// Obtener información del post para mostrar
$sql_post = "SELECT p.*, c.name as category_name 
             FROM posts p 
             LEFT JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ?";
$stmt_post = mysqli_prepare($conectar, $sql_post);
mysqli_stmt_bind_param($stmt_post, "i", $post_id);
mysqli_stmt_execute($stmt_post);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_post));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <!-- Barra de navegación -->
    <?php include('components/navbar.php'); ?>

    <div class="min-h-screen py-6 flex flex-col justify-center sm:py-12">
        <div class="py-3 sm:mx-40">
            <div class="px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                <div class="max-w-md mx-auto">
                    <div class="flex items-center space-x-5">
                        <div class="block pl-2 font-semibold text-xl text-gray-700">
                            <h2 class="leading-relaxed">Confirmar eliminación</h2>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <p class="text-red-600">¡Atención! Esta acción eliminará permanentemente:</p>
                                <ul class="list-disc list-inside mt-2 text-red-500">
                                    <li>El post "<?php echo htmlspecialchars($post['title']); ?>"</li>
                                    <li>Todos los comentarios asociados</li>
                                    <li>Todos los "me gusta" del post</li>
                                </ul>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="font-semibold mb-2">Detalles del post:</h3>
                                <p><span class="font-medium">Título:</span> <?php echo htmlspecialchars($post['title']); ?></p>
                                <p><span class="font-medium">Categoría:</span> <?php echo htmlspecialchars($post['category_name']); ?></p>
                                <p><span class="font-medium">Fecha de publicación:</span> <?php echo date('d/m/Y', strtotime($post['date'])); ?></p>
                            </div>

                            <p class="text-red-600 font-medium">Esta acción no se puede deshacer.</p>
                        </div>

                        <div class="pt-4 flex items-center space-x-4">
                            <a href="inicio.php"
                                class="flex-1 bg-gray-100 text-gray-800 px-4 py-3 rounded-md hover:bg-gray-200 transition duration-200 text-center">
                                Cancelar
                            </a>
                            <form method="POST" class="flex-1">
                                <button type="submit" name="confirm_delete"
                                    class="w-full bg-red-500 text-white px-4 py-3 rounded-md hover:bg-red-600 transition duration-200">
                                    Eliminar Post
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php mysqli_close($conectar); ?>
</body>

</html>