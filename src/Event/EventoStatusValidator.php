<?php

namespace Juancarlos\Regresoacasauabc\Event;

final class EventoStatusValidator
{
    private const ESTADOS_VALIDOS    = ['activo', 'proximo', 'cerrado'];
    private const ESTADOS_REGISTRABLES = ['activo'];

    /**
     * Valida que el estado sea uno de los valores permitidos.
     */
    public static function isValidStatus(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_VALIDOS, true);
    }

    /**
     * Indica si el evento acepta nuevos registros según su estado.
     */
    public static function allowsRegistration(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_REGISTRABLES, true);
    }

    /**
     * Valida que la transición entre dos estados sea permitida.
     * Cualquier cambio entre estados válidos está permitido.
     */
    public static function isValidTransition(string $from, string $to): bool
    {
        if (!self::isValidStatus($from) || !self::isValidStatus($to)) {
            return false;
        }

        return $from !== $to;
    }
}
