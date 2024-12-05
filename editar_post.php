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

// Variable para mensajes
$message = '';
$messageType = '';

// Obtener el user_id desde la tabla authors usando el username
$username = $_SESSION['username'];
$sql_user = "SELECT id FROM authors WHERE name = ?";
$stmt_user = mysqli_prepare($conectar, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
$author_id = $user_data['id'];

// Obtener categorías para el select
$sql_categories = "SELECT * FROM categories";
$categories = mysqli_query($conectar, $sql_categories);

// Obtener estados para el select
$sql_statuses = "SELECT * FROM statuses";
$statuses = mysqli_query($conectar, $sql_statuses);

// Verificar si se proporcionó un ID de post
if (!isset($_GET['id'])) {
    header("Location: inicio.php");
    exit();
}

$post_id = $_GET['id'];

// Obtener los datos del post
$sql_post = "SELECT * FROM posts WHERE id = ? AND author_id = ?";
$stmt_post = mysqli_prepare($conectar, $sql_post);
mysqli_stmt_bind_param($stmt_post, "ii", $post_id, $author_id);
mysqli_stmt_execute($stmt_post);
$result_post =   mysqli_stmt_get_result($stmt_post);

if (mysqli_num_rows($result_post) == 0) {
    header("Location: inicio.php");
    exit();
}

$post = mysqli_fetch_assoc($result_post);

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $title = mysqli_real_escape_string($conectar, $_POST['title']);
        $content = mysqli_real_escape_string($conectar, $_POST['content']);
        $category = mysqli_real_escape_string($conectar, $_POST['category']);
        $status = mysqli_real_escape_string($conectar, $_POST['status']);

        // Manejar la subida de imagen
        $image = $post['image']; // Mantener la imagen existente por defecto
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                throw new Exception("Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.");
            }

            $image = $target_dir . time() . '_' . basename($_FILES["image"]["name"]);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image)) {
                throw new Exception("Error al subir la imagen.");
            }
        }

        // Preparar y ejecutar la consulta SQL
        $sql = "UPDATE posts SET title = ?, content = ?, category_id = ?, image = ?, status_id = ? WHERE id = ? AND author_id = ?";

        $stmt = mysqli_prepare($conectar, $sql);
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . mysqli_error($conectar));
        }

        mysqli_stmt_bind_param($stmt, "ssssiii", $title, $content, $category, $image, $status, $post_id, $author_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Post actualizado exitosamente!";
            $messageType = "success";
            // Actualizar los datos del post
            $post['title'] = $title;
            $post['content'] = $content;
            $post['category_id'] = $category;
            $post['image'] = $image;
            $post['status_id'] = $status;
        } else {
            throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-100" x-data="{ showNotification: <?php echo !empty($message) ? 'true' : 'false'; ?> }">
    <!-- Notificación -->
    <?php if (!empty($message)): ?>
        <div x-show="showNotification"
            x-init="setTimeout(() => showNotification = false, 3000)"
            class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg <?php echo $messageType === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white"
            role="alert">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if ($messageType === 'success'): ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="ml-4">
                    <button @click="showNotification = false" class="inline-flex text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Barra de navegación -->
    <?php include('components/navbar.php'); ?>

    <div class="min-h-screen py-6 flex flex-col justify-center sm:py-12">
        <div class="py-3 sm:mx-40">
            <div class="px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                <div class="max-w-md mx-auto">
                    <div class="flex items-center space-x-5">
                        <div class="block pl-2 font-semibold text-xl text-gray-700">
                            <h2 class="leading-relaxed">Editar post</h2>
                        </div>
                    </div>

                    <form class="divide-y divide-gray-200" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $post_id); ?>" method="POST" enctype="multipart/form-data">
                        <div class="py-8 text-base leading-6 space-y-4 text-gray-700 sm:text-lg sm:leading-7">
                            <div class="flex flex-col">
                                <label class="leading-loose">Título</label>
                                <input type="text" name="title" required
                                    value="<?php echo htmlspecialchars($post['title']); ?>"
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600">
                            </div>

                            <div class="flex flex-col">
                                <label class="leading-loose">Categoría</label>
                                <select name="category" required
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600">
                                    <?php
                                    mysqli_data_seek($categories, 0);
                                    while ($row = mysqli_fetch_assoc($categories)):
                                    ?>
                                        <option value="<?php echo $row['id']; ?>"
                                            <?php echo ($post['category_id'] == $row['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label class="leading-loose">Estado</label>
                                <select name="status" required
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600">
                                    <?php
                                    mysqli_data_seek($statuses, 0);
                                    while ($row = mysqli_fetch_assoc($statuses)):
                                    ?>
                                        <option value="<?php echo $row['id']; ?>"
                                            <?php echo ($post['status_id'] == $row['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label class="leading-loose">Contenido</label>
                                <textarea name="content" required rows="4"
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600"><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>

                            <div class="flex flex-col">
                                <label class="leading-loose">Imagen actual</label>
                                <?php if (!empty($post['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Imagen actual" class="w-full h-32 object-cover rounded-md mb-2">
                                <?php else: ?>
                                    <p class="text-sm text-gray-500">No hay imagen actualmente</p>
                                <?php endif; ?>
                                <label class="leading-loose mt-2">Cambiar imagen (opcional)</label>
                                <input type="file" name="image" accept="image/*"
                                    class="px-4 py-2 border focus:ring-gray-500 focus:border-gray-900 w-full sm:text-sm border-gray-300 rounded-md focus:outline-none text-gray-600">
                                <p class="text-xs text-gray-500 mt-1">Formatos permitidos: JPG, PNG, GIF</p>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center space-x-4">
                            <a href="inicio.php"
                                class="flex justify-center items-center w-full text-gray-900 px-4 py-3 rounded-md focus:outline-none hover:bg-gray-100 transition duration-150 ease-in-out">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="bg-blue-500 flex justify-center items-center w-full text-white px-4 py-3 rounded-md focus:outline-none hover:bg-blue-600 transition duration-150 ease-in-out">
                                Actualizar Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php mysqli_close($conectar); ?>
</body>

</html>