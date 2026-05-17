<?php

namespace App\SharedDomain\Event;

trait EventTrait
{
    private array $events = [];

    protected function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
