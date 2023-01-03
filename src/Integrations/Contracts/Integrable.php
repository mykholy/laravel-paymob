<?php

namespace mykholy\PayMob\Integrations\Contracts;

interface Integrable
{
    public function getPaymentTypeName(): string;
}
