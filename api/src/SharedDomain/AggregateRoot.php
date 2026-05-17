<?php

namespace App\SharedDomain;

interface AggregateRoot
{
    public function releaseEvents();
}
