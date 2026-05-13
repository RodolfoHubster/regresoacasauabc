<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Http\FaqValidator;
use PHPUnit\Framework\TestCase;

final class FaqValidatorTest extends TestCase
{
    public function testFaqValidaCuandoTienePreguntaYRespuesta(): void
    {
        $this->assertTrue(FaqValidator::isValid([
            'pregunta'  => '¿Hay estacionamiento?',
            'respuesta' => 'Sí, el campus cuenta con estacionamiento gratuito.',
        ]));
    }

    public function testFaqInvalidaCuandoPreguntaEstaVacia(): void
    {
        $this->assertFalse(FaqValidator::isValid([
            'pregunta'  => '',
            'respuesta' => 'Sí, hay estacionamiento.',
        ]));
    }

    public function testFaqInvalidaCuandoRespuestaEsSoloEspacios(): void
    {
        $this->assertFalse(FaqValidator::isValid([
            'pregunta'  => '¿Cuál es el código de vestimenta?',
            'respuesta' => '   ',
        ]));
    }

    public function testFaqInvalidaCuandoPreguntaExcedeLimiteDeLongitud(): void
    {
        $preguntaLarga = str_repeat('a', 256);
        $this->assertFalse(FaqValidator::isValid([
            'pregunta'  => $preguntaLarga,
            'respuesta' => 'Respuesta válida.',
        ]));
    }

    public function testFaqInvalidaCuandoRespuestaExcedeLimiteDeLongitud(): void
    {
        $respuestaLarga = str_repeat('b', 1001);
        $this->assertFalse(FaqValidator::isValid([
            'pregunta'  => '¿A qué hora es el acceso?',
            'respuesta' => $respuestaLarga,
        ]));
    }

    public function testFaqInvalidaCuandoFaltanCampos(): void
    {
        $this->assertFalse(FaqValidator::isValid([]));
    }
}
