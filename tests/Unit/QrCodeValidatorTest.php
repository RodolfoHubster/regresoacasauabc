<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\QrCode\QrCodeValidator;
use PHPUnit\Framework\TestCase;

final class QrCodeValidatorTest extends TestCase
{
    public function testCodigoValido(): void
    {
        $this->assertTrue(QrCodeValidator::isValidCode('UABC-TJ-2026-00142'));
    }

    public function testCodigoConCampusDeTresLetras(): void
    {
        $this->assertTrue(QrCodeValidator::isValidCode('UABC-ENS-2026-00001'));
    }

    public function testCodigoVacioEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode(''));
    }

    public function testCodigoConEspaciosSolosEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode('   '));
    }

    public function testCodigoConCaracteresEspecialesEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode('UABC-TJ-2026-@#$%!'));
    }

    public function testCodigoSinPrefixUABCEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode('TJ-2026-00142'));
    }

    public function testCodigoConSecuenciaDeMenosDeCincoDigitosEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode('UABC-TJ-2026-142'));
    }

    public function testCodigoConAnioInvalidoEsInvalido(): void
    {
        $this->assertFalse(QrCodeValidator::isValidCode('UABC-TJ-26-00142'));
    }
}
