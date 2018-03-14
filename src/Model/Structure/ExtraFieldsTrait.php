<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Model\Structure;

use CCT\Component\Rest\Collection\ArrayCollection;
use CCT\Component\Rest\Collection\CollectionInterface;

trait ExtraFieldsTrait
{
    protected $extraFields;

    public function getExtraFields(): CollectionInterface
    {
        if (null === $this->extraFields) {
            $this->extraFields = new ArrayCollection();
        }

        return $this->extraFields;
    }
}
