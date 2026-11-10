<?php

namespace App\Providers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Guardar copia HTML en C:\laragon\bin\sendmail\output\ para Mail Catcher clásico de Laragon
        Event::listen(MessageSent::class, function (MessageSent $event) {
            try {
                $dir = 'C:\\laragon\\bin\\sendmail\\output';
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true, true);
                }

                $html = $event->message->getHtmlBody();
                if (!$html) {
                    $html = nl2br(e($event->message->getTextBody() ?? ''));
                }

                $filename = 'email_' . date('Y-m-d_His') . '_' . uniqid() . '.html';
                File::put($dir . DIRECTORY_SEPARATOR . $filename, $html);
            } catch (\Throwable $e) {
                // Silencioso para no interrumpir el flujo si no hay permisos de disco
            }
        });
    }
}
