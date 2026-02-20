<?php

namespace App\Tests\Controller;

abstract class NoMusicWebTestCase extends AbstractControllerWebTestCase
{
    protected static function seedMode(): string
    {
        return 'no-music';
    }
}
