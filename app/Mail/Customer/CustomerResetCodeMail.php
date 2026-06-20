<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Código de Seguridad - Tienda BoliviaLogistics',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: sans-serif; color: #333;'>
                    <h1 style='color: #2563eb;'>BoliviaLogistics</h1>
                    <p>Has solicitado restablecer tu contraseña. Usa el siguiente código:</p>
                    <div style='background: #f3f4f6; padding: 20px; border-radius: 10px; text-align: center;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #1e40af;'>{$this->code}</span>
                    </div>
                    <p style='font-size: 12px; color: #666; margin-top: 20px;'>
                        Este código expirará en 15 minutos por tu seguridad.
                    </p>
                </div>
            ",
        );
    }
}