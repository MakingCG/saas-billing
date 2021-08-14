<?php

namespace Makingcg\\Subscription;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Makingcg\\Subscription\Subscription
 */
class SubscriptionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'subscription';
    }
}
