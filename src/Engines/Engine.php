<?php

namespace Makingcg\Subscription\Engines;

abstract class Engine
{
    abstract public function hello(): string;

    abstract public function createPlan($data): array;
}
