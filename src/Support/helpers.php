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

if (! function_exists('get_metered_charge_period')) {
    /**
     * Format currency
     */
    function get_metered_charge_period(): string
    {
        $today = now()
            ->format('d. M');

        $startOfThePeriod = now()
            ->subDays(config('subscription.metered_billing.settlement_period'))
            ->format('d. M');

        return "$today - $startOfThePeriod";
    }
}
