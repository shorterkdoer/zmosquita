<?php
namespace App\Core\Helpers;

class DateHelper
{
    /**
     * Valida y convierte una fecha humana (dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy)
     * al formato SQL (yyyy-mm-dd).
     *
     * @param string|null $input
     * @return string|null  Fecha normalizada o null si no es válida
     */
    public static function toSqlDate(?string $input): ?string
    {
        if (!$input) {
            return null;
        }

        // Regex: día, separador, mes, mismo separador, año
        $pattern = '/^(0?[1-9]|[12]\d|3[01])([\/.\-])(0?[1-9]|1[0-2])\2(\d{4})$/';

        if (!preg_match($pattern, $input, $matches)) {
            return null; // formato inválido
        }

        $dia  = (int)$matches[1];
        $mes  = (int)$matches[3];
        $anio = (int)$matches[4];

        if (!checkdate($mes, $dia, $anio)) {
            return null; // fecha imposible (ej: 31/02/2025)
        }

        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    }
}
