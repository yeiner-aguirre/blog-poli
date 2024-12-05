<?php
session_start();

// Chequear si el usuario ya ha iniciado sesión
if (isset($_SESSION['username'])) {
    header("Location: inicio.php");
    exit();
}

// Chequear si hay mensajes de error o de registro
$message = '';
$messageType = '';

if (isset($_SESSION['login_error'])) {
    $message = $_SESSION['login_error'];
    $messageType = 'error';
    unset($_SESSION['login_error']);
} elseif (isset($_SESSION['registration_success'])) {
    $message = $_SESSION['registration_success'];
    $messageType = 'success';
    unset($_SESSION['registration_success']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center" x-data="{ showNotification: <?php echo !empty($message) ? 'true' : 'false'; ?> }">
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
        </div>
        <form action="validar.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Iniciar Sesión
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="registro.php" class="text-sm text-indigo-600 hover:text-indigo-500">¿No tienes cuenta? Crea una</a>
        </div>
    </div>
</body>

</html>