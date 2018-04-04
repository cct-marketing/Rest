<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Definition;

use CCT\Component\Collections\ParameterCollection;

class QueryParams extends ParameterCollection
{
    public function toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        return '?' . http_build_query($this->all());
    }

    public static function create(array $params = [])
    {
        return new static($params);
    }
}
