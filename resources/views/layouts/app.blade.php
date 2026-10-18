<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Face Recognition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow mb-6">
        <div class="max-w-5xl mx-auto px-4 py-3 flex gap-6">
            <a href="{{ route('people.index') }}" class="font-bold text-indigo-600">👤 People</a>
            <a href="{{ route('people.create') }}" class="text-gray-700">➕ Register Face</a>
            <a href="{{ route('recognize') }}" class="text-gray-700">🎯 Recognize</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 pb-10">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
