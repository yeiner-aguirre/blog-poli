<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Obtener el nombre del usuario y su ID
$username = htmlspecialchars($_SESSION['username']);

// Incluir la conexión a la base de datos
include_once('db.php');
$conectar = conn();

// Obtener el ID del autor y su email
$sql_user = "SELECT id, email FROM authors WHERE name = ?";
$stmt_user = mysqli_prepare($conectar, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
$author_id = $user_data['id'];
$author_email = $user_data['email'];

// Consulta SQL para obtener solo los posts del usuario actual
$sql = "SELECT p.*, c.name as category_name, s.name as status_name, 
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as total_comments
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN statuses s ON p.status_id = s.id 
        WHERE p.author_id = ?
        ORDER BY p.date DESC";

$stmt = mysqli_prepare($conectar, $sql);
mysqli_stmt_bind_param($stmt, "i", $author_id);
mysqli_stmt_execute($stmt);
$resul = mysqli_stmt_get_result($stmt);

// Obtener mensaje de notificación si existe
$notification = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$notificationType = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';

// Limpiar las variables de sesión después de obtenerlas
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100" x-data="{ showNotification: <?php echo !empty($notification) ? 'true' : 'false'; ?>, showDeleteModal: false }">
    <!-- Notificación -->
    <?php if (!empty($notification)): ?>
        <div x-show="showNotification"
            x-init="setTimeout(() => showNotification = false, 3000)"
            class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg <?php echo $notificationType === 'success' ? 'bg-green-500' : 'bg-red-500'; ?> text-white"
            role="alert">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <?php if ($notificationType === 'success'): ?>
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
                    <p class="text-sm font-medium"><?php echo htmlspecialchars($notification); ?></p>
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

    <!-- Navegación -->
    <?php include('components/navbar.php'); ?>

    <!-- Contenedor de bienvenida y posts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Bienvenido, <?php echo $username; ?></h1>
            <!-- Botón para eliminar cuenta -->
            <button @click="showDeleteModal = true" class="text-white bg-red-500 hover:bg-red-600 px-2 py-1 rounded-md transition duration-150 ease-in-out">
                <i class="fas fa-user-slash"></i> Eliminar Cuenta
            </button>
        </div>

        <h2 class="text-xl font-semibold text-gray-800 mb-6">Mis Publicaciones</h2>

        <!-- Mostrar posts -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (mysqli_num_rows($resul) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($resul)): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden relative">
                        <!-- Botones de editar y eliminar -->
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <a href="editar_post.php?id=<?php echo $row['id']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eliminar_post.php?id=<?php echo $row['id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="<?php echo htmlspecialchars($row['image']); ?>"
                                alt="Imagen de <?php echo htmlspecialchars($row['title']); ?>"
                                class="w-full h-48 object-cover"
                                onerror="this.src='https://placehold.co/600x400.png?text=No+Image';">
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    <?php echo htmlspecialchars($row['category_name'] ?? 'Sin categoría'); ?>
                                </span>
                                <span class="px-2 py-1 
                                    <?php
                                    // Cambiar el color de fondo según el estado
                                    if ($row['status_name'] === 'Activado') {
                                        echo 'bg-green-100 text-green-800';
                                    } elseif ($row['status_name'] === 'Desactivado') {
                                        echo 'bg-yellow-100 text-yellow-800';
                                    } elseif ($row['status_name'] === 'Borrador') {
                                        echo 'bg-red-100 text-red-800';
                                    } else {
                                        echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    rounded-full text-sm">
                                    <?php echo htmlspecialchars($row['status_name'] ?? 'Sin estado'); ?>
                                </span>
                            </div>

                            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>

                            <p class="text-gray-600 mb-4">
                                <?php
                                $content = htmlspecialchars($row['content']);
                                echo strlen($content) > 150 ? substr($content, 0, 150) . "..." : $content;
                                ?>
                            </p>

                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>Publicado el: <?php echo date('d/m/Y', strtotime($row['date'])); ?></span>
                                <span><?php echo $row['total_likes']; ?> Me gusta</span>
                                <span><?php echo $row['total_comments']; ?> Comentarios</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">No tienes publicaciones todavía.</p>
                    <a href="crear_post.php" class="mt-4 inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Crear tu primer post
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal de confirmación -->
        <div x-show="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-cloak @click.away="showDeleteModal = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar cuenta</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Esta acción no se puede deshacer. Por favor, confirma tu email y contraseña para continuar.
                        </p>
                        <form id="deleteAccountForm" class="mt-4">
                            <input type="email" id="confirmEmail" placeholder="Email" class="mt-2 px-3 py-2 bg-white border shadow-sm border-slate-300 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-sky-500 block w-full rounded-md sm:text-sm focus:ring-1" required>
                            <input type="password" id="confirmPassword" placeholder="Contraseña" class="mt-2 px-3 py-2 bg-white border shadow-sm border-slate-300 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-sky-500 block w-full rounded-md sm:text-sm focus:ring-1" required>
                        </form>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="deleteAccountBtn" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Eliminar cuenta
                        </button>
                        <button @click="showDeleteModal = false" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('deleteAccountBtn').addEventListener('click', function() {
            const email = document.getElementById('confirmEmail').value;
            const password = document.getElementById('confirmPassword').value;

            if (email === '<?php echo $author_email; ?>') {
                fetch('eliminar_cuenta.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Cuenta eliminada',
                                text: 'Tu cuenta ha sido eliminada exitosamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'index.php';
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al procesar tu solicitud. Por favor, verifica tus datos e inténtalo de nuevo.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al procesar tu solicitud.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al procesar tu solicitud. Por favor, verifica tus datos e inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            showDeleteModal = false;
        });
    </script>

    <?php mysqli_close($conectar); ?>
</body>

</html>