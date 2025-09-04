<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Clínica Veterinaria Patitas Felices' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- 🐶 Encabezado -->
    <header class="bg-teal-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">🐾 Patitas Felices</h1>
            <nav>
                <ul class="flex gap-4">
                    <li><a href="/" class="hover:underline">Inicio</a></li>
                    <li><a href="/servicios" class="hover:underline">Servicios</a></li>
                    <li><a href="/citas" class="hover:underline">Citas</a></li>
                    <li><a href="/contacto" class="hover:underline">Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- 🧬 Contenido principal -->
    <main class="container mx-auto py-8 px-4">
        {{ $slot }}
    </main>

    <!-- 🐾 Pie de página -->
    <footer class="bg-teal-700 text-white text-center py-4 mt-8">
        <p>© {{ date('Y') }} Patitas Felices · Todos los derechos reservados</p>
        <p class="text-sm">Cra 45 #123 - Suba, Bogotá · Tel: (601) 555-1234</p>
    </footer>

</body>
</html>