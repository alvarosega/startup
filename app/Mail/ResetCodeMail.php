<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de Recuperación de Contraseña - BoliviaLogistics',
        );
    }

    public function content(): Content
    {
        // Puedes usar una vista markdown o html simple
        return new Content(
            htmlString: '
                <h1>Tu código de seguridad</h1>
                <p>Usa el siguiente código para restablecer tu contraseña:</p>
                <h2 style="font-size: 24px; letter-spacing: 5px;">' . $this->code . '</h2>
                <p>Este código expira en 15 minutos.</p>
            ',
        );
    }
}