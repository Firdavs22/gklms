<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ссылка отправлена - GloboKids</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <span class="text-5xl">🌍</span>
            <h1 class="text-3xl font-bold text-white mt-4">GloboKids</h1>
        </div>

        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Проверьте почту!</h2>
            
            <p class="text-gray-600 mb-6">
                Мы отправили ссылку для входа на<br>
                <strong class="text-gray-900">{{ $email }}</strong>
            </p>

            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                <p class="mb-2">💡 Ссылка действительна 24 часа</p>
                <p>Не пришло письмо? Проверьте папку "Спам"</p>
            </div>

            <a 
                href="{{ route('login') }}" 
                class="inline-block mt-6 text-purple-600 hover:text-purple-800 font-medium"
            >
                ← Вернуться на страницу входа
            </a>
        </div>
    </div>
</body>
</html>
