<?php

declare(strict_types=1);

use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use PHPUnit\Framework\TestCase;

final class RequestValidatorTest extends TestCase
{
    // =========================================================
    // REGISTRO — campos requeridos originales
    // =========================================================

    public function testRegistrationFieldsAreValidWhenAllRequiredValuesExist(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'          => 'Rodolfo',
            'apellidos'       => 'García López',
            'email'           => 'rodolfo@example.com',
            'campus'          => '1',
            'facultad'        => 'FCITEC',
            'carrera'         => 'Ing. Sistemas',
            'generacion'      => '2020',
            'tipo_asistente'  => 'egresado',
            'evento_id'       => '2',
        ]);

        $this->assertTrue($result);
    }

    public function testRegistrationFieldsAreInvalidWhenAnyRequiredValueIsMissingOrBlank(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => '   ',
            'apellidos'      => 'García López',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => 'FCITEC',
            'carrera'        => 'Ing. Sistemas',
            'generacion'     => '2020',
            'tipo_asistente' => 'egresado',
            'evento_id'      => '',
        ]);

        $this->assertFalse($result);
    }

    // =========================================================
    // REGISTRO — campos nuevos del formulario real
    // =========================================================

    public function testRegistrationRequiresApellidos(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => 'Rodolfo',
            'apellidos'      => '',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => 'FCITEC',
            'carrera'        => 'Ing. Sistemas',
            'generacion'     => '2020',
            'tipo_asistente' => 'egresado',
            'evento_id'      => '2',
        ]);

        $this->assertFalse($result);
    }

    public function testRegistrationRequiresFacultad(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => 'Rodolfo',
            'apellidos'      => 'García López',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => '   ',
            'carrera'        => 'Ing. Sistemas',
            'generacion'     => '2020',
            'tipo_asistente' => 'egresado',
            'evento_id'      => '2',
        ]);

        $this->assertFalse($result);
    }

    public function testRegistrationRequiresCarrera(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => 'Rodolfo',
            'apellidos'      => 'García López',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => 'FCITEC',
            'carrera'        => '',
            'generacion'     => '2020',
            'tipo_asistente' => 'egresado',
            'evento_id'      => '2',
        ]);

        $this->assertFalse($result);
    }

    public function testRegistrationRequiresGeneracion(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => 'Rodolfo',
            'apellidos'      => 'García López',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => 'FCITEC',
            'carrera'        => 'Ing. Sistemas',
            'generacion'     => '',
            'tipo_asistente' => 'egresado',
            'evento_id'      => '2',
        ]);

        $this->assertFalse($result);
    }

    public function testRegistrationRequiresTipoAsistente(): void
    {
        $result = RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => 'Rodolfo',
            'apellidos'      => 'García López',
            'email'          => 'rodolfo@example.com',
            'campus'         => '1',
            'facultad'       => 'FCITEC',
            'carrera'        => 'Ing. Sistemas',
            'generacion'     => '2020',
            'tipo_asistente' => '',
            'evento_id'      => '2',
        ]);

        $this->assertFalse($result);
    }

    // =========================================================
    // REGISTRO — validación de formato de email
    // =========================================================

    public function testValidEmailIsAccepted(): void
    {
        $this->assertTrue(RequestValidator::isValidEmail('rodolfo@uabc.edu.mx'));
    }

    public function testEmailWithoutAtSignIsRejected(): void
    {
        $this->assertFalse(RequestValidator::isValidEmail('rodolfouabc.edu.mx'));
    }

    public function testEmailWithoutDomainIsRejected(): void
    {
        $this->assertFalse(RequestValidator::isValidEmail('rodolfo@'));
    }

    public function testEmailWithSpacesIsRejected(): void
    {
        $this->assertFalse(RequestValidator::isValidEmail('rodolfo @uabc.edu.mx'));
    }

    public function testEmptyEmailIsRejected(): void
    {
        $this->assertFalse(RequestValidator::isValidEmail(''));
    }

    // =========================================================
    // REGISTRO — validación de generación (año)
    // =========================================================

    public function testGeneracionValidaEsAceptada(): void
    {
        $this->assertTrue(RequestValidator::isValidGeneracion('2018'));
    }

    public function testGeneracionAntesDe1960EsRechazada(): void
    {
        $this->assertFalse(RequestValidator::isValidGeneracion('1959'));
    }

    public function testGeneracionFuturaEsRechazada(): void
    {
        $anioFuturo = (string)((int)date('Y') + 1);
        $this->assertFalse(RequestValidator::isValidGeneracion($anioFuturo));
    }

    public function testGeneracionNoNumericaEsRechazada(): void
    {
        $this->assertFalse(RequestValidator::isValidGeneracion('veinte'));
    }

    // =========================================================
    // REGISTRO — validación de tipo de asistente
    // =========================================================

    public function testTipoAsistenteValidoEsAceptado(): void
    {
        $this->assertTrue(RequestValidator::isValidTipoAsistente('egresado'));
        $this->assertTrue(RequestValidator::isValidTipoAsistente('docente'));
        $this->assertTrue(RequestValidator::isValidTipoAsistente('administrativo'));
    }

    public function testTipoAsistenteInvalidoEsRechazado(): void
    {
        $this->assertFalse(RequestValidator::isValidTipoAsistente('hacker'));
        $this->assertFalse(RequestValidator::isValidTipoAsistente(''));
        $this->assertFalse(RequestValidator::isValidTipoAsistente('EGRESADO')); // case-sensitive
    }

    // =========================================================
    // EVENTOS — campos requeridos
    // =========================================================

    public function testEventFieldsAreValidWhenAllRequiredValuesExist(): void
    {
        $result = RequestValidator::hasRequiredEventFields([
            'nombre'    => 'Encuentro de Egresados',
            'fecha'     => '2026-08-20',
            'hora'      => '18:00',
            'ubicacion' => 'Mexicali',
        ]);

        $this->assertTrue($result);
    }

    public function testEventFieldsAreInvalidWhenRequiredValueIsEmpty(): void
    {
        $result = RequestValidator::hasRequiredEventFields([
            'nombre'    => 'Encuentro de Egresados',
            'fecha'     => '',
            'hora'      => '18:00',
            'ubicacion' => 'Mexicali',
        ]);

        $this->assertFalse($result);
    }

    public function testEventFieldsAreInvalidWhenOnlySpaces(): void
    {
        $result = RequestValidator::hasRequiredEventFields([
            'nombre'    => '     ',
            'fecha'     => '2026-08-20',
            'hora'      => '18:00',
            'ubicacion' => 'Mexicali',
        ]);

        $this->assertFalse($result);
    }

    // =========================================================
    // EVENTOS — validación de fecha no en el pasado
    // =========================================================

    public function testFechaFuturaEsValida(): void
    {
        $fechaFutura = date('Y-m-d', strtotime('+30 days'));
        $this->assertTrue(RequestValidator::isFutureOrTodayDate($fechaFutura));
    }

    public function testFechaPasadaEsInvalida(): void
    {
        $this->assertFalse(RequestValidator::isFutureOrTodayDate('2020-01-01'));
    }

    public function testFechaDeHoyEsValida(): void
    {
        $hoy = date('Y-m-d');
        $this->assertTrue(RequestValidator::isFutureOrTodayDate($hoy));
    }

    public function testFechaConFormatoInvalidoEsRechazada(): void
    {
        $this->assertFalse(RequestValidator::isFutureOrTodayDate('20-08-2026'));
        $this->assertFalse(RequestValidator::isFutureOrTodayDate('no-es-fecha'));
    }
}
