<?php

namespace App\Services\Auth;

use App\Exceptions\Auth\OtpException;
use App\Mail\LoginOtpCodeMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginOtpService
{
    private const MINUTOS_EXPIRACION = 10;
    private const MAX_INTENTOS = 5;
    private const SEGUNDOS_REENVIO = 60;

    public function generateForUser(User $usuario, Request $request): LoginOtp
    {
        $this->invalidatePreviousOtps($usuario);

        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = LoginOtp::create([
            'id_usuario' => $usuario->id_usuario,
            'email' => $usuario->correo,
            'code_hash' => Hash::make($codigo),
            'expires_at' => now()->addMinutes(self::MINUTOS_EXPIRACION),
            'attempts' => 0,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        Log::info('OTP generado', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id]);

        $this->sendOtp($usuario, $otp, $codigo);

        return $otp;
    }

    public function sendOtp(User $usuario, LoginOtp $otp, string $codigoPlano): void
    {
        $destinatario = config('mail.otp_test_recipient') ?: $usuario->correo;

        Mail::to($destinatario)->send(
            new LoginOtpCodeMail($usuario, $codigoPlano, self::MINUTOS_EXPIRACION)
        );

        Log::info('OTP enviado', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id]);
    }

    public function verify(User $usuario, string $codigo): void
    {
        $otp = LoginOtp::where('id_usuario', $usuario->id_usuario)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if (! $otp) {
            throw new OtpException('No hay un código pendiente. Solicite uno nuevo.');
        }

        if ($otp->expires_at->isPast()) {
            Log::info('OTP expirado', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id]);

            throw new OtpException('El código expiró. Solicite uno nuevo.');
        }

        if ($otp->attempts >= self::MAX_INTENTOS) {
            Log::info('OTP bloqueado por intentos', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id]);

            throw new OtpException('Superó el número máximo de intentos. Solicite un nuevo código.');
        }

        if (! Hash::check($codigo, $otp->code_hash)) {
            $otp->increment('attempts');

            Log::info('OTP fallido', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id, 'intentos' => $otp->attempts]);

            throw new OtpException('Código de verificación incorrecto.');
        }

        $otp->forceFill(['used_at' => now()])->save();

        Log::info('OTP verificado', ['id_usuario' => $usuario->id_usuario, 'otp_id' => $otp->id]);
    }

    public function resend(User $usuario, Request $request): void
    {
        $ultimo = LoginOtp::where('id_usuario', $usuario->id_usuario)->latest('id')->first();

        if ($ultimo && $ultimo->created_at->diffInSeconds(now()) < self::SEGUNDOS_REENVIO) {
            $espera = self::SEGUNDOS_REENVIO - $ultimo->created_at->diffInSeconds(now());

            throw new OtpException("Espere {$espera} segundos antes de solicitar un nuevo código.");
        }

        $this->generateForUser($usuario, $request);
    }

    public function maskEmail(string $correo): string
    {
        [$local, $dominio] = explode('@', $correo, 2);
        $visible = mb_substr($local, 0, 2);

        return $visible.str_repeat('*', max(mb_strlen($local) - 2, 1)).'@'.$dominio;
    }

    public function invalidatePreviousOtps(User $usuario): void
    {
        LoginOtp::where('id_usuario', $usuario->id_usuario)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Fuerza una nueva verificación OTP en el próximo login (uso administrativo).
     */
    public function resetVerification(User $usuario): void
    {
        $usuario->forceFill([
            'otp_verified_at' => null,
            'otp_last_verified_ip' => null,
            'otp_last_verified_user_agent' => null,
        ])->save();
    }
}
