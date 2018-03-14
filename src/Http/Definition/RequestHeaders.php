<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Definition;

use CCT\Component\Rest\Collection\ArrayCollection;

class RequestHeaders extends ArrayCollection
{
    public static function create($params = [])
    {
        return new static($params);
    }
}
