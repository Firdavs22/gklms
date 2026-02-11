<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход - Растем вместе</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            light: '#D0E3F4',
                            peach: '#F1C49E',
                            green: '#83C696',
                            blue: '#4A91CD',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .phone-input::-webkit-outer-spin-button,
        .phone-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body class="bg-brand-light min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Растем вместе" class="h-28 mx-auto">
            <h1 class="text-3xl font-bold text-brand-blue mt-4">Растем вместе</h1>
            <p class="text-gray-600 mt-2">С нами будущее будет образовано</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6 text-center">Войти в кабинет</h2>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Phone Auth Form (Telegram only) -->
            <div id="form-phone" class="space-y-4">
                <div id="phone-step-1">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Номер телефона
                    </label>
                    <input type="tel" id="phone" required placeholder="+7 (999) 123-45-67"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition phone-input">
                    <p class="text-sm text-gray-500 mt-2">
                        Введите номер, привязанный к вашему Telegram
                    </p>

                    <button type="button" id="btn-request-phone" onclick="requestPhoneAuth()"
                        class="w-full mt-4 bg-brand-blue text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition shadow-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z" />
                        </svg>
                        Войти через Telegram
                    </button>
                </div>

                <!-- Step 2a: Waiting for confirmation via deep link (new users) -->
                <div id="phone-step-2-deeplink" class="hidden text-center">
                    <div class="mb-4">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-blue mx-auto"></div>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Подтвердите номер в Telegram</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Перейдите в бота и нажмите "Поделиться номером телефона"
                    </p>
                    <a id="telegram-link" href="#" target="_blank"
                        class="inline-flex items-center gap-2 bg-blue-500 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-600 transition">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z" />
                        </svg>
                        Открыть Telegram
                    </a>
                    <p class="text-xs text-gray-400 mt-4">
                        Автоматическая проверка каждые 2 секунды...
                    </p>
                    <button type="button" onclick="cancelPhoneAuth()"
                        class="mt-4 text-sm text-gray-500 hover:text-gray-700">
                        ← Вернуться
                    </button>
                </div>

                <!-- Step 2b: Enter code from Telegram (registered users) -->
                <div id="phone-step-2-code" class="hidden text-center">
                    <div class="mb-4">
                        <div class="rounded-full h-12 w-12 bg-blue-100 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Введите код из Telegram</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Мы отправили 6-значный код в ваш Telegram
                    </p>

                    <input type="text" id="telegram-code" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                        placeholder="000000"
                        class="w-40 mx-auto block text-center text-2xl tracking-widest px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition">

                    <p id="code-error" class="text-sm text-red-500 mt-2 hidden"></p>

                    <button type="button" id="btn-verify-code" onclick="verifyCode()"
                        class="w-full mt-4 bg-brand-blue text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition shadow-lg">
                        Подтвердить
                    </button>

                    <button type="button" id="btn-resend-code" onclick="resendCode()"
                        class="mt-3 text-sm text-brand-blue hover:underline disabled:text-gray-400 disabled:no-underline"
                        disabled>
                        Отправить код повторно (<span id="resend-timer">60</span>с)
                    </button>

                    <button type="button" onclick="cancelPhoneAuth()"
                        class="mt-2 block mx-auto text-sm text-gray-500 hover:text-gray-700">
                        ← Вернуться
                    </button>
                </div>

                <!-- Step 3: Success -->
                <div id="phone-step-3" class="hidden text-center">
                    <div class="mb-4">
                        <div class="rounded-full h-12 w-12 bg-green-100 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Авторизация успешна!</h3>
                    <p class="text-sm text-gray-600">Перенаправление...</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-sm mt-8">
            Нет аккаунта? Приобретите курс на
            <a href="https://globokids.ru" class="text-brand-blue underline hover:no-underline">globokids.ru</a>
        </p>
    </div>

    <script>
        let authToken = null;
        let authPhone = null;
        let authMode = null; // 'code' or 'deeplink'
        let pollInterval = null;
        let resendInterval = null;

        async function requestPhoneAuth() {
            const phone = document.getElementById('phone').value;
            if (!phone || phone.length < 10) {
                alert('Введите корректный номер телефона');
                return;
            }

            const btn = document.getElementById('btn-request-phone');
            btn.disabled = true;
            btn.innerHTML = '<span class="animate-spin">⏳</span> Подождите...';

            try {
                const response = await fetch('{{ route("auth.phone") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone }),
                });

                const data = await response.json();

                if (data.success) {
                    authMode = data.mode;
                    authPhone = phone;

                    document.getElementById('phone-step-1').classList.add('hidden');

                    if (data.mode === 'code') {
                        // Registered user — show code input
                        document.getElementById('phone-step-2-code').classList.remove('hidden');
                        document.getElementById('telegram-code').focus();
                        startResendTimer();
                    } else {
                        // New user — show deep link
                        authToken = data.token;
                        document.getElementById('telegram-link').href = data.deep_link;
                        document.getElementById('phone-step-2-deeplink').classList.remove('hidden');
                        startPolling();
                    }
                } else {
                    alert(data.message || 'Произошла ошибка');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Произошла ошибка. Попробуйте ещё раз.');
            }

            btn.disabled = false;
            btn.innerHTML = `
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                </svg>
                Войти через Telegram
            `;
        }

        async function verifyCode() {
            const code = document.getElementById('telegram-code').value;
            const errorEl = document.getElementById('code-error');

            if (!code || code.length !== 6) {
                errorEl.textContent = 'Введите 6-значный код';
                errorEl.classList.remove('hidden');
                return;
            }

            const btn = document.getElementById('btn-verify-code');
            btn.disabled = true;
            btn.textContent = 'Проверка...';
            errorEl.classList.add('hidden');

            try {
                const response = await fetch('{{ route("auth.phone.verify-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: authPhone, code }),
                });

                const data = await response.json();

                if (data.success) {
                    // Show success
                    document.getElementById('phone-step-2-code').classList.add('hidden');
                    document.getElementById('phone-step-3').classList.remove('hidden');

                    // Redirect
                    setTimeout(() => {
                        window.location.href = data.redirect || '/dashboard';
                    }, 1000);
                } else {
                    errorEl.textContent = data.message || 'Неверный код';
                    errorEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Подтвердить';
                }
            } catch (error) {
                console.error('Error:', error);
                errorEl.textContent = 'Произошла ошибка. Попробуйте ещё раз.';
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Подтвердить';
            }
        }

        async function resendCode() {
            const btn = document.getElementById('btn-resend-code');
            btn.disabled = true;

            try {
                const response = await fetch('{{ route("auth.phone") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: authPhone }),
                });

                const data = await response.json();

                if (data.success && data.mode === 'code') {
                    const errorEl = document.getElementById('code-error');
                    errorEl.textContent = 'Новый код отправлен!';
                    errorEl.classList.remove('hidden');
                    errorEl.classList.remove('text-red-500');
                    errorEl.classList.add('text-green-600');
                    setTimeout(() => {
                        errorEl.classList.add('hidden');
                        errorEl.classList.add('text-red-500');
                        errorEl.classList.remove('text-green-600');
                    }, 3000);
                    document.getElementById('telegram-code').value = '';
                    document.getElementById('telegram-code').focus();
                    startResendTimer();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function startResendTimer() {
            let seconds = 60;
            const timerEl = document.getElementById('resend-timer');
            const btn = document.getElementById('btn-resend-code');
            btn.disabled = true;

            if (resendInterval) clearInterval(resendInterval);

            resendInterval = setInterval(() => {
                seconds--;
                timerEl.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(resendInterval);
                    btn.disabled = false;
                    btn.innerHTML = 'Отправить код повторно';
                }
            }, 1000);
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);

            pollInterval = setInterval(async () => {
                try {
                    const response = await fetch(`{{ route("auth.phone.status") }}?token=${authToken}`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        clearInterval(pollInterval);

                        // Show success
                        document.getElementById('phone-step-2-deeplink').classList.add('hidden');
                        document.getElementById('phone-step-3').classList.remove('hidden');

                        // Redirect
                        setTimeout(() => {
                            window.location.href = data.redirect || '/dashboard';
                        }, 1000);
                    } else if (data.status === 'expired') {
                        clearInterval(pollInterval);
                        alert('Время сессии истекло. Попробуйте снова.');
                        cancelPhoneAuth();
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000);
        }

        function cancelPhoneAuth() {
            if (pollInterval) clearInterval(pollInterval);
            if (resendInterval) clearInterval(resendInterval);
            authToken = null;
            authPhone = null;
            authMode = null;

            document.getElementById('phone-step-1').classList.remove('hidden');
            document.getElementById('phone-step-2-deeplink').classList.add('hidden');
            document.getElementById('phone-step-2-code').classList.add('hidden');
            document.getElementById('phone-step-3').classList.add('hidden');

            // Reset code input
            const codeInput = document.getElementById('telegram-code');
            if (codeInput) codeInput.value = '';
            const codeError = document.getElementById('code-error');
            if (codeError) codeError.classList.add('hidden');
        }

        // Allow submitting code with Enter key
        document.addEventListener('DOMContentLoaded', () => {
            const codeInput = document.getElementById('telegram-code');
            if (codeInput) {
                codeInput.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter' && codeInput.value.length === 6) {
                        verifyCode();
                    }
                });
            }
        });
    </script>
</body>

</html>