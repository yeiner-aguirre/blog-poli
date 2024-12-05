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

// Obtener el ID del usuario actual
$username = $_SESSION['username'];
$sql_user = "SELECT id, name, email FROM authors WHERE name = ?";
$stmt_user = mysqli_prepare($conectar, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);
$current_user_id = $user_data['id'];

// Obtener todas las categorías
$sql_categories = "SELECT c.*, COUNT(p.id) as post_count 
                  FROM categories c 
                  LEFT JOIN posts p ON c.id = p.category_id AND p.status_id = 1 
                  GROUP BY c.id";
$categories_result = mysqli_query($conectar, $sql_categories);

// Obtener todos los autores
$sql_authors = "SELECT a.*, COUNT(p.id) as post_count 
                FROM authors a 
                LEFT JOIN posts p ON a.id = p.author_id AND p.status_id = 1 
                GROUP BY a.id";
$authors_result = mysqli_query($conectar, $sql_authors);

// Procesar filtros
$where_conditions = ["p.status_id = 1"];
$params = [];
$types = "";

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $_GET['category'];
    $types .= "i";
}

if (isset($_GET['author']) && !empty($_GET['author'])) {
    $where_conditions[] = "p.author_id = ?";
    $params[] = $_GET['author'];
    $types .= "i";
}

// Agregar condición para búsqueda por título
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "p.title LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= "s";
}

// Consulta SQL para obtener posts con filtros
$sql = "SELECT 
            p.id,
            p.title,
            p.content,
            p.image,
            p.date,
            c.name as category_name,
            a.name as author_name,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND author_id = ?) as user_liked
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN authors a ON p.author_id = a.id
        WHERE " . implode(" AND ", $where_conditions) . "
        ORDER BY p.date DESC";

$stmt = mysqli_prepare($conectar, $sql);

// Agregar el current_user_id al inicio de los parámetros
array_unshift($params, $current_user_id);
$types = "i" . $types;

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$resul = mysqli_stmt_get_result($stmt);

if (!$resul) {
    die("Error en la consulta: " . mysqli_error($conectar));
}

// Procesar solicitud AJAX
if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];

    if ($_POST['action'] === 'like') {
        $post_id = $_POST['post_id'];

        // Verificar si ya existe el like
        $check_like = "SELECT id FROM likes WHERE author_id = ? AND post_id = ?";
        $stmt_check = mysqli_prepare($conectar, $check_like);
        mysqli_stmt_bind_param($stmt_check, "ii", $current_user_id, $post_id);
        mysqli_stmt_execute($stmt_check);
        $like_result = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($like_result) == 0) {
            // Si no existe, crear el like
            $sql_like = "INSERT INTO likes (author_id, user_id, post_id) VALUES (?, ?, ?)";
            $stmt_like = mysqli_prepare($conectar, $sql_like);
            mysqli_stmt_bind_param($stmt_like, "iii", $current_user_id, $current_user_id, $post_id);
            if (mysqli_stmt_execute($stmt_like)) {
                $response['success'] = true;
                $response['message'] = 'Like agregado correctamente';
            }
        } else {
            // Si existe, eliminar el like
            $sql_unlike = "DELETE FROM likes WHERE author_id = ? AND post_id = ?";
            $stmt_unlike = mysqli_prepare($conectar, $sql_unlike);
            mysqli_stmt_bind_param($stmt_unlike, "ii", $current_user_id, $post_id);
            if (mysqli_stmt_execute($stmt_unlike)) {
                $response['success'] = true;
                $response['message'] = 'Like removido correctamente';
            }
        }

        // Obtener el nuevo conteo de likes
        $sql_count = "SELECT COUNT(*) as count FROM likes WHERE post_id = ?";
        $stmt_count = mysqli_prepare($conectar, $sql_count);
        mysqli_stmt_bind_param($stmt_count, "i", $post_id);
        mysqli_stmt_execute($stmt_count);
        $count_result = mysqli_stmt_get_result($stmt_count);
        $count_data = mysqli_fetch_assoc($count_result);
        $response['likes_count'] = $count_data['count'];
    } elseif ($_POST['action'] === 'comment') {
        $post_id = $_POST['post_id'];
        $comment = $_POST['comment'];

        $sql_comment = "INSERT INTO comments (post_id, author_id, user_id, comment, date) VALUES (?, ?, ?, ?, NOW())";
        $stmt_comment = mysqli_prepare($conectar, $sql_comment);
        mysqli_stmt_bind_param($stmt_comment, "iiis", $post_id, $current_user_id, $current_user_id, $comment);
        if (mysqli_stmt_execute($stmt_comment)) {
            $response['success'] = true;
            $response['message'] = 'Comment added successfully';

            // Obtener el ID del nuevo comentario
            $comment_id = mysqli_insert_id($conectar);
            $sql_get_comment = "SELECT c.*, a.name as commenter_name FROM comments c JOIN authors a ON c.author_id = a.id WHERE c.id = ?";
            $stmt_get_comment = mysqli_prepare($conectar, $sql_get_comment);
            mysqli_stmt_bind_param($stmt_get_comment, "i", $comment_id);
            mysqli_stmt_execute($stmt_get_comment);
            $comment_result = mysqli_stmt_get_result($stmt_get_comment);
            $comment_data = mysqli_fetch_assoc($comment_result);

            $response['comment'] = [
                'id' => $comment_data['id'],
                'commenter_name' => $comment_data['commenter_name'],
                'comment' => $comment_data['comment'],
                'date' => date('d/m/Y', strtotime($comment_data['date']))
            ];
        }
    } elseif ($_POST['action'] === 'update_profile') {
        $name = $_POST['name'];
        $email = $_POST['email'];

        $sql_update = "UPDATE authors SET name = ?, email = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conectar, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $name, $email, $current_user_id);
        if (mysqli_stmt_execute($stmt_update)) {
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully';
            $_SESSION['username'] = $name;
        }
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .post-image {
            aspect-ratio: 16/9;
            width: 100%;
            object-fit: cover;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Navegación -->
    <?php include('components/navbar.php'); ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Barra lateral -->
            <div class="w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Categorías</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="explorar.php"
                                class="text-gray-600 hover:text-indigo-600 transition-colors duration-200 <?php echo !isset($_GET['category']) ? 'font-semibold text-indigo-600' : ''; ?>">
                                Todas las categorías
                            </a>
                        </li>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)):
                            $isActive = isset($_GET['category']) && $_GET['category'] == $cat['id'];
                        ?>
                            <li>
                                <a href="?category=<?php echo $cat['id']; ?><?php echo isset($_GET['author']) ? '&author=' . $_GET['author'] : ''; ?>"
                                    class="flex items-center justify-between text-gray-600 hover:text-indigo-600 transition-colors duration-200 <?php echo $isActive ? 'font-semibold text-indigo-600' : ''; ?>">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs">
                                        <?php echo $cat['post_count']; ?>
                                    </span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Autores</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="explorar.php<?php echo isset($_GET['category']) ? '?category=' . $_GET['category'] : ''; ?>"
                                class="text-gray-600 hover:text-indigo-600 transition-colors duration-200 <?php echo !isset($_GET['author']) ? 'font-semibold text-indigo-600' : ''; ?>">
                                Todos los autores
                            </a>
                        </li>
                        <?php while ($author = mysqli_fetch_assoc($authors_result)):
                            $isActive = isset($_GET['author']) && $_GET['author'] == $author['id'];
                        ?>
                            <li>
                                <a href="?author=<?php echo $author['id']; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?>"
                                    class="flex items-center justify-between text-gray-600 hover:text-indigo-600 transition-colors duration-200 <?php echo $isActive ? 'font-semibold text-indigo-600' : ''; ?>">
                                    <span><?php echo htmlspecialchars($author['name']); ?></span>
                                    <span class="bg-gray-200 text-gray-700 rounded-full px-2 py-1 text-xs">
                                        <?php echo $author['post_count']; ?>
                                    </span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- Contenedor principal de posts -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">
                    <?php
                    // Cambiar el título según la categoría o autor seleccionado
                    if (isset($_GET['category']) && isset($_GET['author'])) {
                        $category_id = $_GET['category'];
                        $author_id = $_GET['author'];

                        // Obtener el nombre de la categoría
                        $sql_category_name = "SELECT name FROM categories WHERE id = ?";
                        $stmt_category_name = mysqli_prepare($conectar, $sql_category_name);
                        mysqli_stmt_bind_param($stmt_category_name, "i", $category_id);
                        mysqli_stmt_execute($stmt_category_name);
                        $result_category_name = mysqli_stmt_get_result($stmt_category_name);
                        $category = mysqli_fetch_assoc($result_category_name);

                        // Obtener el nombre del autor
                        $sql_author_name = "SELECT name FROM authors WHERE id = ?";
                        $stmt_author_name = mysqli_prepare($conectar, $sql_author_name);
                        mysqli_stmt_bind_param($stmt_author_name, "i", $author_id);
                        mysqli_stmt_execute($stmt_author_name);
                        $result_author_name = mysqli_stmt_get_result($stmt_author_name);
                        $author = mysqli_fetch_assoc($result_author_name);

                        echo "Explorar publicaciones de " . htmlspecialchars($category['name']) . " por " . htmlspecialchars($author['name']);
                    } elseif (isset($_GET['category'])) {
                        $category_id = $_GET['category'];
                        $sql_category_name = "SELECT name FROM categories WHERE id = ?";
                        $stmt_category_name = mysqli_prepare($conectar, $sql_category_name);
                        mysqli_stmt_bind_param($stmt_category_name, "i", $category_id);
                        mysqli_stmt_execute($stmt_category_name);
                        $result_category_name = mysqli_stmt_get_result($stmt_category_name);
                        $category = mysqli_fetch_assoc($result_category_name);
                        echo "Explorar publicaciones de " . htmlspecialchars($category['name']);
                    } elseif (isset($_GET['author'])) {
                        $author_id = $_GET['author'];
                        $sql_author_name = "SELECT name FROM authors WHERE id = ?";
                        $stmt_author_name = mysqli_prepare($conectar, $sql_author_name);
                        mysqli_stmt_bind_param($stmt_author_name, "i", $author_id);
                        mysqli_stmt_execute($stmt_author_name);
                        $result_author_name = mysqli_stmt_get_result($stmt_author_name);
                        $author = mysqli_fetch_assoc($result_author_name);
                        echo "Explorar publicaciones de " . htmlspecialchars($author['name']);
                    } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
                        echo "Resultados de búsqueda para: " . htmlspecialchars($_GET['search']);
                    } else {
                        echo "Explorar todas las publicaciones";
                    }
                    ?>
                </h1>

                <!-- Barra de búsqueda -->
                <div class="mb-4">
                    <form method="GET" action="explorar.php" class="flex">
                        <input type="text" name="search" placeholder="Buscar por título..." class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:border-indigo-500 mr-2" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200">Buscar</button>
                    </form>
                </div>

                <!-- Mostrar posts -->
                <div class="space-y-6">
                    <?php if (mysqli_num_rows($resul) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($resul)): ?>
                            <div id="post-<?php echo $row['id']; ?>"
                                class="bg-white rounded-lg shadow-lg overflow-hidden"
                                x-data="{ showComments: false }">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($row['author_name']); ?>
                                            </span>
                                            <span class="text-gray-500">•</span>
                                            <span class="text-gray-500">
                                                <?php echo date('d/m/Y', strtotime($row['date'])); ?>
                                            </span>
                                        </div>
                                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm">
                                            <?php echo htmlspecialchars($row['category_name'] ?? 'Sin categoría'); ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($row['image'])): ?>
                                        <div class="mb-4 rounded-lg overflow-hidden">
                                            <img src="<?php echo htmlspecialchars($row['image']); ?>"
                                                alt="Imagen de <?php echo htmlspecialchars($row['title']); ?>"
                                                class="post-image"
                                                onerror="this.src='https://placehold.co/600x400/png';">
                                        </div>
                                    <?php endif; ?>

                                    <h2 class="text-xl font-semibold text-gray-800 mb-2">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </h2>

                                    <p class="text-gray-600 mb-4">
                                        <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                                    </p>

                                    <div class="flex items-center justify-between border-t pt-4">
                                        <div class="flex items-center space-x-4">
                                            <button @click="likePost(<?php echo $row['id']; ?>)" id="like-button-<?php echo $row['id']; ?>" class="like-button flex items-center space-x-1 text-gray-500 hover:text-indigo-600 transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 <?php echo $row['user_liked'] ? 'text-indigo-600 fill-current' : ''; ?>"
                                                    viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                                </svg>
                                                <span id="likes-count-<?php echo $row['id']; ?>"><?php echo $row['likes_count']; ?></span>
                                            </button>

                                            <button @click="showComments = !showComments" class="flex items-center space-x-1 text-gray-500 hover:text-indigo-600 transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                                    <path d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" />
                                                </svg>
                                                <span><?php echo $row['comments_count']; ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sección de comentarios -->
                                <div x-show="showComments" class="border-t bg-gray-50 p-6">
                                    <!-- Formulario para nuevo comentario -->
                                    <form @submit.prevent="addComment(<?php echo $row['id']; ?>)" class="mb-4">
                                        <div class="flex gap-2">
                                            <input type="text" name="comment" x-ref="commentInput<?php echo $row['id']; ?>"
                                                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:border-indigo-500"
                                                placeholder="Escribe un comentario..." required>
                                            <button type="submit"
                                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                                Comentar
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Lista de comentarios -->
                                    <div id="comments-list-<?php echo $row['id']; ?>" class="space-y-4">
                                        <?php
                                        $sql_comments = "SELECT c.*, a.name as commenter_name 
                                                       FROM comments c 
                                                       JOIN authors a ON c.author_id = a.id 
                                                       WHERE c.post_id = ? 
                                                       ORDER BY c.date DESC";
                                        $stmt_comments = mysqli_prepare($conectar, $sql_comments);
                                        mysqli_stmt_bind_param($stmt_comments, "i", $row['id']);
                                        mysqli_stmt_execute($stmt_comments);
                                        $comments = mysqli_stmt_get_result($stmt_comments);

                                        while ($comment = mysqli_fetch_assoc($comments)):
                                        ?>
                                            <div class="bg-white p-4 rounded-lg shadow">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-semibold text-gray-800">
                                                        <?php echo htmlspecialchars($comment['commenter_name']); ?>
                                                    </span>
                                                    <span class="text-sm text-gray-500">
                                                        <?php echo date('d/m/Y', strtotime($comment['date'])); ?>
                                                    </span>
                                                </div>
                                                <p class="text-gray-600">
                                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                                </p>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-lg shadow p-6 text-center">
                            <p class="text-gray-600">No hay publicaciones disponibles.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function likePost(postId) {
            fetch('explorar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=like&post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likesCountElement = document.getElementById(`likes-count-${postId}`);
                        likesCountElement.textContent = data.likes_count;

                        // Cambiar el color del botón de "me gusta"
                        const likeButton = document.getElementById(`like-button-${postId}`);
                        if (likeButton.querySelector('svg').classList.contains('text-indigo-600')) {
                            likeButton.querySelector('svg').classList.remove('text-indigo-600', 'fill-current');
                        } else {
                            likeButton.querySelector('svg').classList.add('text-indigo-600', 'fill-current');
                        }
                    }
                });
        }

        function addComment(postId) {
            const commentInput = document.querySelector(`[x-ref="commentInput${postId}"]`);
            const comment = commentInput.value;

            fetch('explorar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=comment&post_id=${postId}&comment=${encodeURIComponent(comment)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const commentsList = document.getElementById(`comments-list-${postId}`);
                        const newComment = document.createElement('div');
                        newComment.className = 'bg-white p-4 rounded-lg shadow';
                        newComment.innerHTML = `
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold text-gray-800">${data.comment.commenter_name}</span>
                            <span class="text-sm text-gray-500">${data.comment.date}</span>
                        </div>
                        <p class="text-gray-600">${data.comment.comment}</p>
                    `;
                        commentsList.insertBefore(newComment, commentsList.firstChild);
                        commentInput.value = '';
                    }
                });
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('profileData', () => ({
                profileName: '<?php echo htmlspecialchars($user_data['name']); ?>',
                profileEmail: '<?php echo htmlspecialchars($user_data['email']); ?>',
                updateProfile() {
                    fetch('explorar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=update_profile&name=${encodeURIComponent(this.profileName)}&email=${encodeURIComponent(this.profileEmail)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Perfil actualizado exitosamente');
                                this.$refs.editProfileModal.close();
                            } else {
                                alert('Error al actualizar el perfil');
                            }
                        });
                }
            }));
        });
    </script>

    <?php mysqli_close($conectar); ?>
</body>

</html>