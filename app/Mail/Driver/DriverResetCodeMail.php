<?php

namespace App\Mail\Driver;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class DriverResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Código de Acceso - Panel de Conductor');
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: sans-serif; border: 2px solid #f59e0b; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #1e293b;'>BoliviaLogistics <span style='color: #f59e0b;'>Drivers</span></h2>
                    <p>Usa este código para recuperar tu acceso al panel logístico:</p>
                    <div style='background: #fffbeb; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #b45309;'>
                        {$this->code}
                    </div>
                    <p style='font-size: 11px; color: #64748b; margin-top: 15px;'>Válido por 15 minutos.</p>
                </div>
            ",
        );
    }
}