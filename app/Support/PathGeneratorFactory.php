<?php

namespace App\Support;

use App\Contracts\PathGenerator;
use App\Exceptions\InvalidPathGenerator;

class PathGeneratorFactory
{
    public static function create(): PathGenerator
    {
        $class = config('lets_encrypt.path_generator');

        throw_if($class === null, new InvalidPathGenerator('null'));

        throw_if(! class_exists($class), new InvalidPathGenerator($class));

        return app($class);
    }
}
