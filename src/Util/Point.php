<?php

namespace App\Util;

class Point
{
    public $coords;

    public function __construct($longitude, $latitude)
    {
        $this->coords = [$longitude, $latitude];
    }
}
