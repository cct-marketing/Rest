<?php

namespace CCT\Component\Rest\Model\Structure;

use CCT\Component\Rest\Collection\CollectionInterface;

interface ExtraFieldsInterface
{
    /**
     * Gets the collection of extra fields.
     *
     * @return CollectionInterface
     */
    public function getExtraFields(): CollectionInterface;
}
