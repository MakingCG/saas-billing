<?php

if (! function_exists('format_currency')) {
    /**
     * Format currency
     */
    function format_currency($item): string
    {
        $formatter = numfmt_create('en_EN', NumberFormatter::CURRENCY);

        return numfmt_format_currency($formatter, $item->amount, $item->currency);
    }
}
