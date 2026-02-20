<?php

namespace App\Tests\Controller;

abstract class WithMusicWebTestCase extends AbstractControllerWebTestCase
{
    protected static function seedMode(): string
    {
        return 'with-music';
    }
}
