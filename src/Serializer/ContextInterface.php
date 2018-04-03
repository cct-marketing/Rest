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
    public function setAttribute($key, $value): self;

    /** Checks if contains a normalization attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute($key): bool;

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
    public function getAttributes(): array;

    /**
     * Sets the normalization version.
     *
     * @param string|null $version
     *
     * @return self
     */
    public function setVersion($version): self;

    /**
     * Gets the normalization version.
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Adds a normalization group.
     *
     * @param string $group
     *
     * @return self
     */
    public function addGroup($group): self;

    /**
     * Adds normalization groups.
     *
     * @param string[] $groups
     *
     * @return self
     */
    public function addGroups(array $groups): self;

    /**
     * Gets the normalization groups.
     *
     * @return string[]|null
     */
    public function getGroups(): ?array;

    /**
     * Set the normalization groups.
     *
     * @param string[]|null $groups
     *
     * @return self
     */
    public function setGroups(array $groups = null): self;


    /**
     * @return bool|null
     */
    public function isMaxDepthEnabled(): ?bool;

    /**
     * Sets serialize null.
     *
     * @param bool|null $serializeNull
     *
     * @return self
     */
    public function setSerializeNull($serializeNull): self;

    /**
     * Gets serialize null.
     *
     * @return bool|null
     */
    public function getSerializeNull(): ?bool;

    /* Gets the array of exclusion strategies.
    *
    * Notice: This method only applies to the JMS serializer adapter.
    *
    * @return array|ExclusionStrategyInterface[]
    */
    public function getExclusionStrategies(): array;

    /**
     * Adds an exclusion strategy.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @param ExclusionStrategyInterface $exclusionStrategy
     */
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy);

    /**
     * Gets the normalization max depth.
     *
     * @return int|null
     *
     */
    public function getMaxDepth(): ?int;
}
