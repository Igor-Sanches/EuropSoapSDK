<?php

if(!function_exists('format_telephone')){
    /**
     * @param $value
     * @return array|string|string[]|null
     */
    function format_telephone($value): array|string|null
    {
        if(empty($value)){
            return null;
        }
        $tel = preg_replace('/\D/', '', $value);
        return preg_replace('/^(\d{2})(\d{8,9})$/', '$1 $2', $tel);
    }
}

if(!function_exists('type_person_europ')){
    function type_person_europ($person_type): string
    {
        return $person_type == "PF" ? "Fisica" : "Juridica";
    }
}

if(!function_exists('format_cpf')){
    /**
     * @param $value
     * @return string|null
     */
    function format_cpf($value): ?string
    {
        if(empty($value)){
            return null;
        }
        $document = preg_replace('/\D/', '', $value);
        return str_pad($document, 20, '0', STR_PAD_LEFT);
    }
}

if(!function_exists('format_datetime_europ')){
    /**
     * @param $value
     * @return string|null
     */
    function format_datetime_europ($value): ?string
    {
        if(empty($value)){
            return null;
        }

        // Converter a data de 2025-12-02 para 02122025, formato aceito pela Europ

        $split = explode('-', $value);
        $year = $split[0];
        $month = $split[1];
        $day = $split[2];
        return "$day$month$year";
    }
}
