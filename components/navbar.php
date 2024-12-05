<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<nav class="bg-white shadow-lg sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="flex space-x-7">
                <div class="flex items-center py-4">
                    <i class="fas fa-user-circle text-gray-500 mr-2"></i>
                    <span class="text-gray-500"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </div>
            <div class="hidden md:flex items-center space-x-3">
                <a href="inicio.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="explorar.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-search"></i> Explorar
                </a>
                <a href="crear_post.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-plus"></i> Crear Post
                </a>
                <a href="logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
            <div class="md:hidden flex items-center">
                <button id="menu-toggle" class="text-gray-500 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden flex flex-col space-y-2 mt-2 pb-4">
            <a href="inicio.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-home"></i> Inicio
            </a>
            <a href="explorar.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-search"></i> Explorar
            </a>
            <a href="crear_post.php" class="py-2 px-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-plus"></i> Crear Post
            </a>
            <a href="logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</nav>
<script>
    document.getElementById('menu-toggle').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>