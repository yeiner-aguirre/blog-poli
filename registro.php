<?php
session_start();
include_once('db.php');

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Todos los campos son obligatorios.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Formato de email inválido.";
        $messageType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Las contraseñas no coinciden.";
        $messageType = "error";
    } else {
        $conectar = conn();

        // Chequear si el nuevo usuario existe
        $check_sql = "SELECT * FROM authors WHERE name = ? OR email = ?";
        $check_stmt = mysqli_prepare($conectar, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "El nombre de usuario o email ya está en uso.";
            $messageType = "error";
        } else {
            $hashed_password = $password;
            // Inserta nuevo usuario
            $insert_sql = "INSERT INTO authors (name, email, password) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conectar, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "sss", $username, $email, $hashed_password);

            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['registration_success'] = "Registro exitoso. Ahora puedes iniciar sesión.";
                header("Location: index.php");
                exit();
            } else {
                $message = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
                $messageType = "error";
            }
        }

        mysqli_close($conectar);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center" x-data="{ showNotification: <?php echo !empty($message) ? 'true' : 'false'; ?> }">
    <!-- Notificación -->
    <?php if (!empty($message)): ?>
        <div x-show="showNotification"
            x-init="setTimeout(() => showNotification = false, 5000)"
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

    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <div class="text-center mb-8">
            <img src="imagenes/logo.png" alt="Logo de la Aplicación" class="mx-auto w-32">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Crear una cuenta</h2>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nombre de usuario</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Registrarse
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="index.php" class="text-sm text-indigo-600 hover:text-indigo-500">¿Ya tienes una cuenta? Inicia sesión</a>
        </div>
    </div>
</body>

</html>