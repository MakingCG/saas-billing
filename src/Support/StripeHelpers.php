<?php

function mapStripeStatus($status): string
{
    return match ($status) {
        'active', 'trialing' => 'active',
        'canceled', 'unpaid' => 'cancelled',
        'incomplete' => 'inactive',
        'incomplete_expired', 'past_due' => 'completed',
    };
}
