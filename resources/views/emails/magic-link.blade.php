<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f3f4f6; margin: 0; padding: 40px 20px;">
    <div style="max-width: 480px; margin: 0 auto;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 32px;">
            <span style="font-size: 40px;">🌍</span>
            <h1 style="color: #111827; font-size: 24px; margin: 16px 0 0 0;">GloboKids</h1>
        </div>

        <!-- Card -->
        <div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="color: #111827; font-size: 20px; margin: 0 0 16px 0;">Вход в личный кабинет</h2>
            
            <p style="color: #6b7280; font-size: 16px; line-height: 1.5; margin: 0 0 24px 0;">
                Привет! Нажмите кнопку ниже, чтобы войти в личный кабинет GloboKids:
            </p>

            <!-- Button -->
            <a 
                href="{{ $magicLink }}" 
                style="display: block; background: linear-gradient(135deg, #9333ea 0%, #db2777 100%); color: white; text-decoration: none; text-align: center; padding: 14px 24px; border-radius: 10px; font-weight: 600; font-size: 16px;"
            >
                Войти в личный кабинет
            </a>

            <p style="color: #9ca3af; font-size: 14px; margin: 24px 0 0 0; text-align: center;">
                Ссылка действительна 24 часа
            </p>
        </div>

        <!-- Footer -->
        <p style="color: #9ca3af; font-size: 13px; text-align: center; margin-top: 32px;">
            Если вы не запрашивали вход, просто проигнорируйте это письмо.
        </p>
    </div>
</body>
</html>
