<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Http\Definition;

use CCT\Component\Rest\Collection\ArrayCollection;

class QueryParams extends ArrayCollection
{
    public function toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        return '?' . http_build_query($this->toArray());
    }

    public static function create(array $params = [])
    {
        return new static($params);
    }
}
