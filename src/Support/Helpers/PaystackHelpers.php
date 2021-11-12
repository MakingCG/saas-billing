<?php

/**
 * Map intervals from request.input.interval
 */
function mapPaystackIntervals(string $interval): string
{
    return match ($interval) {
        'day'   => 'daily',
        'week'  => 'weekly',
        'month' => 'monthly',
        'year'  => 'annually',
    };
}
