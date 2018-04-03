<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCT\Component\Rest\Serializer\Context;

use CCT\Component\Rest\Serializer\ContextInterface;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;

/**
 * Stores the serialization or deserialization context (groups, version, ...).
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class Context implements ContextInterface
{
    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var array|null
     */
    private $groups;

    /**
     * @var int|null
     */
    private $maxDepth;

    /**
     * @var bool
     */
    private $isMaxDepthEnabled;

    /**
     * @var bool|null
     */
    private $serializeNull;

    /**
     * @var ExclusionStrategyInterface[]
     */
    private $exclusionStrategies = array();

    /**
     * Sets an attribute.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return ContextInterface
     */
    public function setAttribute($key, $value): ContextInterface
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Checks if contains a normalization attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute($key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Gets an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        return null;
    }

    /**
     * Gets the attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Sets the normalization version.
     *
     * @param string|null $version
     *
     * @return ContextInterface
     */
    public function setVersion($version): ContextInterface
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets the normalization version.
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Adds a normalization group.
     *
     * @param string $group
     *
     * @return ContextInterface
     */
    public function addGroup($group): ContextInterface
    {
        if (null === $this->groups) {
            $this->groups = [];
        }
        if (!\in_array($group, $this->groups, true)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * Adds normalization groups.
     *
     * @param string[] $groups
     *
     * @return ContextInterface
     */
    public function addGroups(array $groups): ContextInterface
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Gets the normalization groups.
     *
     * @return string[]|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * Set the normalization groups.
     *
     * @param string[]|null $groups
     *
     * @return ContextInterface
     */
    public function setGroups(array $groups = null): ContextInterface
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Sets the normalization max depth.
     *
     * @param int|null $maxDepth
     *
     * @return ContextInterface
     */
    public function setMaxDepth($maxDepth): ContextInterface
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Gets the normalization max depth.
     *
     * @return int|null
     *
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    public function enableMaxDepth()
    {
        $this->isMaxDepthEnabled = true;

        return $this;
    }

    public function disableMaxDepth()
    {
        $this->isMaxDepthEnabled = false;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isMaxDepthEnabled(): ?bool
    {
        return $this->isMaxDepthEnabled;
    }

    /**
     * Sets serialize null.
     *
     * @param bool|null $serializeNull
     *
     * @return ContextInterface
     */
    public function setSerializeNull($serializeNull): ContextInterface
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    /**
     * Gets serialize null.
     *
     * @return bool|null
     */
    public function getSerializeNull(): ?bool
    {
        return $this->serializeNull;
    }

    /**
     * Gets the array of exclusion strategies.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @return ExclusionStrategyInterface[]
     */
    public function getExclusionStrategies(): array
    {
        return $this->exclusionStrategies;
    }

    /**
     * Adds an exclusion strategy.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @param ExclusionStrategyInterface $exclusionStrategy
     */
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy): void
    {
        $this->exclusionStrategies[] = $exclusionStrategy;
    }

    public static function create()
    {
        return new static();
    }
}
