<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de correo</title>
</head>
<body>
    <p>Hola {{ $user->name ?? 'usuario' }},</p>

    <p>Necesitamos verificar tu dirección de correo electrónico.</p>

    <p>
        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:10px 20px;background:#D52B1E;color:#fff;text-decoration:none;border-radius:4px;">
            Verificar correo
        </a>
    </p>

    <p style="word-break:break-all;">
        Si el botón no funciona, usá este enlace:<br>
        {{ $verificationUrl }}
    </p>

    <p>Si no creaste una cuenta, podés ignorar este mensaje.</p>

    <p>Saludos,<br>El equipo de {{ config('app.name') }}</p>
</body>
</html>
