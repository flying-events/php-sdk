<?php

namespace FlyingEvents;

use MyCLabs\Enum\Enum;

class Environment extends Enum
{

    private const LIVE = 'LIVE';
    private const TEST = 'TEST';

    public static function LIVE()
    {
        return self::LIVE;
    }

    public static function TEST()
    {
        return self::TEST;
    }
}
