<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GloboKids LMS</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: var(--tg-theme-bg-color, #ffffff);
            color: var(--tg-theme-text-color, #000000);
        }
        .loading-spinner {
            border: 3px solid rgba(124, 58, 237, 0.2);
            border-top-color: #7c3aed;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div id="loading" class="text-center">
        <div class="loading-spinner mx-auto mb-4"></div>
        <p class="text-gray-600">Загрузка...</p>
    </div>

    <script>
        // Initialize Telegram Web App
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();

        // Set theme
        document.body.style.backgroundColor = tg.themeParams.bg_color || '#ffffff';
        document.body.style.color = tg.themeParams.text_color || '#000000';

        // Get init data and authenticate
        const initData = tg.initData;

        if (initData) {
            // Send to server for authentication
            fetch('/webapp/auth', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Telegram-Init-Data': initData,
                },
                body: JSON.stringify({ initData: initData })
            })
            .then(response => response.json())
            .then(data => {
                if (data.authenticated && data.redirect) {
                    // Redirect to dashboard
                    window.location.href = data.redirect;
                } else {
                    // Show error
                    document.getElementById('loading').innerHTML = `
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600">Ошибка авторизации</p>
                            <button onclick="tg.close()" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg">
                                Закрыть
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Auth error:', error);
                document.getElementById('loading').innerHTML = `
                    <div class="text-center">
                        <p class="text-red-500">Ошибка: ${error.message}</p>
                        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg">
                            Повторить
                        </button>
                    </div>
                `;
            });
        } else {
            // No initData - redirect to regular login
            window.location.href = '/login';
        }
    </script>
</body>
</html>
