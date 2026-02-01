<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GloboKids LMS</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 320px;
            width: 90%;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f0f0f0;
            border-top-color: #764ba2;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        h2 { color: #333; margin-bottom: 10px; font-size: 20px; }
        p { color: #666; font-size: 14px; }
        .error { color: #e74c3c; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .debug { 
            margin-top: 20px; 
            font-size: 11px; 
            color: #999; 
            word-break: break-all;
            max-height: 100px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="card" id="content">
        <div class="spinner"></div>
        <h2>Загрузка...</h2>
        <p>Подключение к GloboKids LMS</p>
    </div>

    <script>
        const content = document.getElementById('content');
        
        try {
            // Initialize Telegram Web App
            const tg = window.Telegram?.WebApp;
            
            if (!tg) {
                throw new Error('Telegram WebApp не доступен');
            }
            
            tg.ready();
            tg.expand();

            // Get init data
            const initData = tg.initData;
            const initDataUnsafe = tg.initDataUnsafe;
            
            if (!initData) {
                // No initData - show message
                content.innerHTML = `
                    <h2>👋 Добро пожаловать!</h2>
                    <p>Откройте эту страницу из Telegram бота GlobokidsAuth</p>
                    <a href="https://t.me/GlobokidsAuthBot" class="btn">Открыть бота</a>
                `;
            } else {
                // Has initData - redirect to auth endpoint
                const authUrl = '/webapp/auth-redirect?initData=' + encodeURIComponent(initData);
                window.location.href = authUrl;
            }
            
        } catch (error) {
            content.innerHTML = `
                <h2 class="error">Ошибка</h2>
                <p>${error.message}</p>
                <button class="btn" onclick="location.reload()">Повторить</button>
                <div class="debug">Debug: ${error.stack || 'no stack'}</div>
            `;
        }
    </script>
</body>
</html>
