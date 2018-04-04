<?php

declare(strict_types=1);

namespace CCT\Component\Rest\Model\Structure;

use CCT\Component\Collections\ArrayCollection;
use CCT\Component\Collections\CollectionInterface;

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
