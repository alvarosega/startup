<!DOCTYPE html>
<html>
<head>
    <title>Recuperación de Contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: white; padding: 20px; border-radius: 10px; text-align: center;">
        <h2 style="color: #333;">Solicitud de Restablecimiento</h2>
        <p>Has solicitado restablecer tu contraseña. Usa el siguiente código de seguridad:</p>
        
        <div style="font-size: 32px; font-weight: bold; color: #2563eb; letter-spacing: 5px; margin: 20px 0;">
            {{ $code }}
        </div>
        
        <p style="font-size: 12px; color: #666;">Este código expirará en 15 minutos.</p>
        <p style="font-size: 12px; color: #999;">Si no solicitaste este cambio, ignora este correo.</p>
    </div>
</body>
</html>