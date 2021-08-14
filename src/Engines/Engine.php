<?php

namespace Makingcg\Subscription\Engines;

interface Engine
{
    public function hello(): string;

    public function createPlan($data): array;
}
