<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Definition;

use CCT\Component\Collections\ParameterCollection;

class RequestHeaders extends ParameterCollection
{
    public static function create(array $params = [])
    {
        return new static($params);
    }
}
