<?php

namespace CCT\Component\Rest\Serializer;

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;

interface ContextInterface
{
    /**
     * Sets an attribute.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function setAttribute($key, $value);

    /** Checks if contains a normalization attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute($key);

    /**
     * Gets an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Gets the attributes.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Sets the normalization version.
     *
     * @param int|null $version
     *
     * @return self
     */
    public function setVersion($version);

    /**
     * Gets the normalization version.
     *
     * @return int|null
     */
    public function getVersion();

    /**
     * Adds a normalization group.
     *
     * @param string $group
     *
     * @return self
     */
    public function addGroup($group);

    /**
     * Adds normalization groups.
     *
     * @param string[] $groups
     *
     * @return self
     */
    public function addGroups(array $groups);

    /**
     * Gets the normalization groups.
     *
     * @return string[]|null
     */
    public function getGroups();

    /**
     * Set the normalization groups.
     *
     * @param string[]|null $groups
     *
     * @return self
     */
    public function setGroups(array $groups = null);


    /**
     * @return bool|null
     */
    public function isMaxDepthEnabled();

    /**
     * Sets serialize null.
     *
     * @param bool|null $serializeNull
     *
     * @return self
     */
    public function setSerializeNull($serializeNull);

    /**
     * Gets serialize null.
     *
     * @return bool|null
     */
    public function getSerializeNull();

    /* Gets the array of exclusion strategies.
    *
    * Notice: This method only applies to the JMS serializer adapter.
    *
    * @return ExclusionStrategyInterface[]
    */
    public function getExclusionStrategies();

    /**
     * Adds an exclusion strategy.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @param ExclusionStrategyInterface $exclusionStrategy
     */
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy);
}
