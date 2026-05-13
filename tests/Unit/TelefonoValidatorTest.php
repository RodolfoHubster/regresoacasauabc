<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Http\TelefonoValidator;
use PHPUnit\Framework\TestCase;

final class TelefonoValidatorTest extends TestCase
{
    public function testTelefonoVacioEsValido(): void
    {
        // El teléfono es opcional
        $this->assertTrue(TelefonoValidator::isValidOrEmpty(''));
    }

    public function testTelefonoConSoloEspaciosEsValido(): void
    {
        // Espacios equivalen a vacío (opcional)
        $this->assertTrue(TelefonoValidator::isValidOrEmpty('   '));
    }

    public function testTelefonoLocalEsValido(): void
    {
        $this->assertTrue(TelefonoValidator::isValidOrEmpty('6641234567'));
    }

    public function testTelefonoFormateadoEsValido(): void
    {
        $this->assertTrue(TelefonoValidator::isValidOrEmpty('(664) 123-4567'));
    }

    public function testTelefonoConPrefijoInternacionalEsValido(): void
    {
        $this->assertTrue(TelefonoValidator::isValidOrEmpty('+52 664 123 4567'));
    }

    public function testTelefonoDeMasiadoCortoEsInvalido(): void
    {
        $this->assertFalse(TelefonoValidator::isValidOrEmpty('123'));
    }

    public function testTelefonoConLetrasEsInvalido(): void
    {
        $this->assertFalse(TelefonoValidator::isValidOrEmpty('abcdefghij'));
    }

    public function testTelefonoConCaracteresRarosEsInvalido(): void
    {
        $this->assertFalse(TelefonoValidator::isValidOrEmpty('664#123$456'));
    }
}
