<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Event\EventoStatusValidator;
use PHPUnit\Framework\TestCase;

final class EventoStatusValidatorTest extends TestCase
{
    public function testEstadosValidosSonAceptados(): void
    {
        $this->assertTrue(EventoStatusValidator::isValidStatus('activo'));
        $this->assertTrue(EventoStatusValidator::isValidStatus('proximo'));
        $this->assertTrue(EventoStatusValidator::isValidStatus('cerrado'));
    }

    public function testEstadoInvalidoEsRechazado(): void
    {
        $this->assertFalse(EventoStatusValidator::isValidStatus('publicado'));
        $this->assertFalse(EventoStatusValidator::isValidStatus(''));
        $this->assertFalse(EventoStatusValidator::isValidStatus('ACTIVO')); // case-sensitive
    }

    public function testEventoActivoPermiteRegistro(): void
    {
        $this->assertTrue(EventoStatusValidator::allowsRegistration('activo'));
    }

    public function testEventoCerradoNoPermiteRegistro(): void
    {
        $this->assertFalse(EventoStatusValidator::allowsRegistration('cerrado'));
    }

    public function testEventoProximoNoPermiteRegistro(): void
    {
        $this->assertFalse(EventoStatusValidator::allowsRegistration('proximo'));
    }

    public function testTransicionDeActivoACerradoEsValida(): void
    {
        $this->assertTrue(EventoStatusValidator::isValidTransition('activo', 'cerrado'));
    }

    public function testTransicionDeCerradoAActivoEsValida(): void
    {
        $this->assertTrue(EventoStatusValidator::isValidTransition('cerrado', 'activo'));
    }

    public function testTransicionAlMismoEstadoEsInvalida(): void
    {
        $this->assertFalse(EventoStatusValidator::isValidTransition('activo', 'activo'));
    }

    public function testTransicionConEstadoInvalidoEsRechazada(): void
    {
        $this->assertFalse(EventoStatusValidator::isValidTransition('activo', 'borrador'));
    }
}
