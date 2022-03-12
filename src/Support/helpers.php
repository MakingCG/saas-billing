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

if (! function_exists('is_demo')) {
    /**
     * Check if is demo
     */
    function is_demo(): bool
    {
        return config('subscription.is_demo');
    }
}

if (! function_exists('is_demo_account')) {
    /**
     * Check if is demo environment
     */
    function is_demo_account(): bool
    {
        return config('subscription.is_demo') && auth()->user()->email === 'howdy@hi5ve.digital';
    }
}
