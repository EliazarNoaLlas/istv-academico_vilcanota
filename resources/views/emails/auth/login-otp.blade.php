<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Código de verificación</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:24px; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:8px; overflow:hidden;">
        <tr>
            <td style="background:#0a3d62; padding:20px 24px;">
                <span style="color:#ffffff; font-size:16px; font-weight:700;">ISTV Vilcanota</span>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p style="margin:0 0 12px;">Hola {{ $usuario->nombres }},</p>
                <p style="margin:0 0 20px;">Tu código de verificación para iniciar sesión en el Sistema Académico ISTV es:</p>
                <p style="margin:0 0 20px; font-size:32px; font-weight:800; letter-spacing:6px; text-align:center; color:#0a3d62;">
                    {{ $codigo }}
                </p>
                <p style="margin:0 0 12px;">Este código vence en {{ $minutosExpiracion }} minutos.</p>
                <p style="margin:0; font-size:12px; color:#6b7280;">Si usted no solicitó este código, ignore este mensaje.</p>
            </td>
        </tr>
    </table>
</body>
</html>
