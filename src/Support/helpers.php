<?php

if (! function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency(float $amount, string $currency): string
    {
        $formatter = numfmt_create('en_EN', NumberFormatter::CURRENCY);

        return numfmt_format_currency($formatter, $amount, $currency);
    }
}
