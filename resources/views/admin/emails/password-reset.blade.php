<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
</head>
<body>
    <p>Hola {{ $user->name ?? 'usuario' }},</p>

    <p>Recibimos una solicitud para restablecer tu contraseña.</p>

    <p>Hacé clic en el siguiente botón o copiá y pegá el enlace en tu navegador:</p>

    <p>
        <a href="{{ $resetUrl }}" style="display:inline-block;padding:10px 20px;background:#D52B1E;color:#fff;text-decoration:none;border-radius:4px;">
            Restablecer contraseña
        </a>
    </p>

    <p style="word-break:break-all;">
        Si el botón no funciona, usá este enlace:<br>
        {{ $resetUrl }}
    </p>

    <p>Si no solicitaste este cambio, podés ignorar este mensaje.</p>

    <p>Saludos,<br>El equipo de {{ config('app.name') }}</p>
</body>
</html>
