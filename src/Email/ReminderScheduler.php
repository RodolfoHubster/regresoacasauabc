<?php

namespace Juancarlos\Regresoacasauabc\Email;

final class ReminderScheduler
{
    private const DIAS_ANTES = 2;

    /**
     * Determina si hoy corresponde enviar el recordatorio para un evento.
     * El recordatorio se envía exactamente DIAS_ANTES días antes del evento.
     *
     * @param string $fechaEvento Fecha del evento en formato YYYY-MM-DD
     * @param string $hoy         Fecha actual en formato YYYY-MM-DD (inyectable para tests)
     */
    public static function debeEnviarHoy(string $fechaEvento, string $hoy = ''): bool
    {
        if ($hoy === '') {
            $hoy = date('Y-m-d');
        }

        $evento   = date_create_from_format('Y-m-d', $fechaEvento);
        $fechaHoy = date_create_from_format('Y-m-d', $hoy);

        if ($evento === false || $fechaHoy === false) {
            return false;
        }

        $diff = (int) $fechaHoy->diff($evento)->days;
        $esFuturo = $evento > $fechaHoy;

        return $esFuturo && $diff === self::DIAS_ANTES;
    }
}
