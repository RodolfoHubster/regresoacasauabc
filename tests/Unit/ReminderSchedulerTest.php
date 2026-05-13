<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Email\ReminderScheduler;
use PHPUnit\Framework\TestCase;

final class ReminderSchedulerTest extends TestCase
{
    public function testDebeEnviarCuandoFaltanExactamenteDiasDias(): void
    {
        $hoy         = '2026-08-18';
        $fechaEvento = '2026-08-20'; // 2 días después
        $this->assertTrue(ReminderScheduler::debeEnviarHoy($fechaEvento, $hoy));
    }

    public function testNoDebeEnviarCuandoFaltanTresDias(): void
    {
        $hoy         = '2026-08-17';
        $fechaEvento = '2026-08-20'; // 3 días después
        $this->assertFalse(ReminderScheduler::debeEnviarHoy($fechaEvento, $hoy));
    }

    public function testNoDebeEnviarCuandoFaltaUnDia(): void
    {
        $hoy         = '2026-08-19';
        $fechaEvento = '2026-08-20'; // mañana
        $this->assertFalse(ReminderScheduler::debeEnviarHoy($fechaEvento, $hoy));
    }

    public function testNoDebeEnviarElMismoDiaDelEvento(): void
    {
        $hoy         = '2026-08-20';
        $fechaEvento = '2026-08-20';
        $this->assertFalse(ReminderScheduler::debeEnviarHoy($fechaEvento, $hoy));
    }

    public function testNoDebeEnviarCuandoElEventoYaPaso(): void
    {
        $hoy         = '2026-08-21';
        $fechaEvento = '2026-08-20'; // ayer
        $this->assertFalse(ReminderScheduler::debeEnviarHoy($fechaEvento, $hoy));
    }

    public function testNoDebeEnviarCuandoFechaEsInvalida(): void
    {
        $this->assertFalse(ReminderScheduler::debeEnviarHoy('no-es-fecha', '2026-08-18'));
    }
}
