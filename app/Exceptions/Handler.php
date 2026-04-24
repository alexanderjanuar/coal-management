<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Exception types that are too common/expected to warrant a webhook ping.
     */
    protected array $skipWebhookFor = [
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ];

    /**
     * Redirect unauthenticated users to the correct Filament panel login.
     * Laravel's default looks for a 'login' route which doesn't exist here.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        return redirect()->guest(route('filament.admin.auth.login'));
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->sendErrorWebhook($e);
        });
    }

    private function sendErrorWebhook(Throwable $e): void
    {
        $webhookUrl = config('app.discord_webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        foreach ($this->skipWebhookFor as $skipped) {
            if ($e instanceof $skipped) {
                return;
            }
        }

        try {
            // Deduplicate — skip if same error was sent recently
            $cacheKey = 'discord_error:' . md5(get_class($e) . $e->getMessage() . $e->getFile() . $e->getLine());
            $cooldownMinutes = (int) env('DISCORD_ERROR_COOLDOWN_MINUTES', 15);

            if (Cache::has($cacheKey)) {
                return;
            }

            Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));

            $request = request();
            $appName  = config('app.name');
            $env      = config('app.env');

            // Build mention string from comma-separated IDs
            $mentionIds = array_filter(explode(',', config('app.discord_mention_ids', '')));
            $mentions   = implode(' ', array_map(fn($id) => '<@' . trim($id) . '>', $mentionIds));

            Http::timeout(5)->post($webhookUrl, [
                'content' => $mentions ?: null,
                'embeds' => [
                    [
                        'title'       => '🚨 Application Error',
                        'color'       => 0xE74C3C, // red
                        'description' => '```' . $e->getMessage() . '```',
                        'fields'      => [
                            [
                                'name'   => '📦 Exception',
                                'value'  => '`' . get_class($e) . '`',
                                'inline' => false,
                            ],
                            [
                                'name'   => '📄 File',
                                'value'  => '`' . str_replace(base_path() . '/', '', $e->getFile()) . ':' . $e->getLine() . '`',
                                'inline' => false,
                            ],
                            [
                                'name'   => '🌐 URL',
                                'value'  => $request?->fullUrl() ?? 'N/A',
                                'inline' => true,
                            ],
                            [
                                'name'   => '📡 Method',
                                'value'  => $request?->method() ?? 'N/A',
                                'inline' => true,
                            ],
                            [
                                'name'   => '🌍 Environment',
                                'value'  => $env,
                                'inline' => true,
                            ],
                        ],
                        'footer'    => ['text' => $appName],
                        'timestamp' => now()->toIso8601String(),
                    ],
                ],
            ]);
        } catch (Throwable) {
            // Silently fail — never let the webhook call break the app
            Log::warning('Discord error webhook delivery failed for: ' . $e->getMessage());
        }
    }
}
