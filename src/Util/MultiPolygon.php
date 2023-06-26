<?php

namespace App\Util;

class MultiPolygon
{
    public $components;

    public function __construct($components)
    {
        $this->components = $components;
    }
}
