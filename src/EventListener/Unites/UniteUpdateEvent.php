<?php

namespace App\EventListener\Unites;


use Symfony\Contracts\EventDispatcher\Event;

class UniteUpdateEvent extends Event
{
    public const NAME = 'unite.updated';
    
}