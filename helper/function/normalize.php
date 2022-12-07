<?php

if (!function_exists('ck')) {

    /**
     * Verifica se uma condicao é verdadeira
     * @param boolean $condicao
     * @return int Se a condição e verdadeira
     */
    function ck(mixed $condicao): bool
    {
        return intval(boolval($condicao));
    }
}

if (!function_exists('concat')) {

    /** Concatena duas ou mais strigs fornecidas */
    function concat(): string
    {
        return implode('', func_get_args());
    }
}