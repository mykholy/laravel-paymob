<?php

namespace mykholy\PayMob\Integrations\Contracts;

interface Billable
{
    public function getBillingData(): array;
}
