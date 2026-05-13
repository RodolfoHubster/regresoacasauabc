<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use PHPUnit\Framework\TestCase;

final class RequestValidatorTest extends TestCase
{
    public function testRegistrationFieldsAreValidWhenAllRequiredValuesExist(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre' => 'Rodolfo',
            'email' => 'rodolfo@example.com',
            'campus' => '1',
            'evento_id' => '2',
        ]);

        $this->assertTrue($result);
    }

    public function testRegistrationFieldsAreInvalidWhenAnyRequiredValueIsMissingOrBlank(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre' => '   ',
            'email' => 'rodolfo@example.com',
            'campus' => '1',
            'evento_id' => '',
        ]);

        $this->assertFalse($result);
    }

    public function testEventFieldsAreValidWhenAllRequiredValuesExist(): void
    {
        $result = RequestValidator::hasRequiredEventFields([
            'nombre' => 'Encuentro de Egresados',
            'fecha' => '2026-08-20',
            'hora' => '18:00',
            'ubicacion' => 'Mexicali',
        ]);

        $this->assertTrue($result);
    }

    public function testEventFieldsAreInvalidWhenRequiredValueIsEmpty(): void
    {
        $result = RequestValidator::hasRequiredEventFields([
            'nombre' => 'Encuentro de Egresados',
            'fecha' => '',
            'hora' => '18:00',
            'ubicacion' => 'Mexicali',
        ]);

        $this->assertFalse($result);
    }
}
