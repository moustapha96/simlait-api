<?php

namespace App\EventListener\Unites;

use Symfony\Contracts\EventDispatcher\Event;

class UniteCreateEvent extends Event
{
    public const NAME = 'unite.created';

}