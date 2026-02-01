<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class AdminLogin extends BaseLogin
{
    /**
     * Admin Telegram IDs allowed for 2FA
     */
    protected array $allowedAdminTelegramIds = [
        '402582319', // Firdavs
    ];

    public ?string $telegram_code = null;

    public function mount(): void
    {
        parent::mount();
        
        // Reset step on fresh page load (but not on Livewire updates)
        if (!request()->isMethod('post')) {
            Session::forget('admin_login_step');
        }
    }

    protected function getStep(): int
    {
        return Session::get('admin_login_step', 1);
    }

    protected function setStep(int $step): void
    {
        Session::put('admin_login_step', $step);
    }

    public function form(Form $form): Form
    {
        if ($this->getStep() === 2) {
            return $form
                ->schema([
                    TextInput::make('telegram_code')
                        ->label('Код из Telegram')
                        ->placeholder('Введите 6-значный код')
                        ->required()
                        ->numeric()
                        ->minLength(6)
                        ->maxLength(6)
                        ->autofocus(),
                ])
                ->statePath('data');
        }

        return $form
            ->schema([
                TextInput::make('email')
                    ->label('Логин')
                    ->required()
                    ->autofocus()
                    ->autocomplete('username'),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->required()
                    ->autocomplete('current-password'),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        // Step 1: Verify login and password
        if ($this->getStep() === 1) {
            $validUsername = env('ADMIN_LOGIN', '22GKlms');
            $validPassword = env('ADMIN_PASSWORD', 'AVjUvnjk34');
            
            if ($data['email'] !== $validUsername) {
                throw ValidationException::withMessages([
                    'data.email' => 'Неверный логин',
                ]);
            }
            
            if ($data['password'] !== $validPassword) {
                throw ValidationException::withMessages([
                    'data.password' => 'Неверный пароль',
                ]);
            }

            // Generate and send 2FA code to Telegram
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store code in cache for 5 minutes
            Cache::put('admin_2fa_code', $code, now()->addMinutes(5));
            
            // Send code to Telegram
            $this->sendTelegramCode($code);
            
            // Move to step 2
            $this->setStep(2);
            
            // Redirect to refresh the page with new form
            return null;
        }

        // Step 2: Verify Telegram code
        if ($this->getStep() === 2) {
            $storedCode = Cache::get('admin_2fa_code');
            $inputCode = $data['telegram_code'] ?? '';
            
            if (!$storedCode || $inputCode !== $storedCode) {
                throw ValidationException::withMessages([
                    'data.telegram_code' => 'Неверный код',
                ]);
            }

            // Clear the code and step
            Cache::forget('admin_2fa_code');
            Session::forget('admin_login_step');

            // Find or create admin user
            $user = \App\Models\User::where('telegram_id', $this->allowedAdminTelegramIds[0])->first();
            
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => 'Admin',
                    'email' => 'admin@globokidsedu.ru',
                    'telegram_id' => $this->allowedAdminTelegramIds[0],
                    'password' => bcrypt('random_' . uniqid()),
                ]);
            }

            // Login the user
            auth()->login($user, true);

            session()->regenerate();

            return app(LoginResponse::class);
        }

        return null;
    }

    /**
     * Send 2FA code to Telegram
     */
    protected function sendTelegramCode(string $code): void
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            return;
        }

        foreach ($this->allowedAdminTelegramIds as $telegramId) {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $telegramId,
                'text' => "🔐 *Код для входа в админку*\n\n`{$code}`\n\n_Действителен 5 минут_",
                'parse_mode' => 'Markdown',
            ]);
        }
    }

    public function getHeading(): string
    {
        return $this->getStep() === 1 ? 'Вход в админку' : 'Подтверждение';
    }

    public function getSubheading(): ?string
    {
        return $this->getStep() === 2 ? 'Введите код из Telegram' : null;
    }
}
