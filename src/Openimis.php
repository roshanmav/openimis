<?php

namespace Insurance\Openimis;

class Openimis
{
    /**
     * Indicates if openimis routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static();
    }
}
