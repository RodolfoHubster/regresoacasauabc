<?php

namespace Juancarlos\Regresoacasauabc\QrCode;

final class QrCodeValidator
{
    /**
     * Formato esperado: UABC-{CAMPUS}-{AÑO}-{SECUENCIA 5 dígitos}
     * Ejemplo: UABC-TJ-2026-00142
     */
    private const PATTERN = '/^UABC-[A-Z]{2,3}-\d{4}-\d{5}$/';

    /**
     * Valida que un código QR tenga el formato correcto del sistema.
     */
    public static function isValidCode(string $code): bool
    {
        $code = trim($code);

        if ($code === '') {
            return false;
        }

        return preg_match(self::PATTERN, $code) === 1;
    }
}
