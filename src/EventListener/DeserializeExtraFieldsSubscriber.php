<?php

declare(strict_types=1);

namespace CCT\Component\Rest\EventListener;

use CCT\Component\Rest\Collection\CollectionInterface;
use CCT\Component\Rest\Model\Structure\ExtraFieldsInterface;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class DeserializeExtraFieldsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected static $config;

    /**
     * It sets the default configuration for the DeserializeExtraFields.
     * The array must be sent in following structure:
     * $config = [
     *    'FQDN' => [list of fields to ignore],
     * ];
     *
     * Example:
     * $config = [
     *    'Kong\\Model\\Kong' => ["tagline"],
     * ];
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        self::$config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $subscribers = [];

        foreach (self::$config as $class => $ignoreFields) {
            if (!class_exists($class)) {
                continue;
            }

            $subscribers[] = self::createPreDeserializeSubscriber($class);
            $subscribers[] = self::createPostDeserializeSubscriber($class);
        }

        return $subscribers;
    }

    /**
     * Create pre deserialize subscriber
     *
     * @param $class
     *
     * @return array
     */
    protected static function createPreDeserializeSubscriber($class)
    {
        return [
            'event' => 'serializer.pre_deserialize',
            'method' => 'onPreDeserialize',
            'class' => $class,
            'format' => 'json',
            'priority' => 0,
        ];
    }

    /**
     * Create post deserialize subscriber
     *
     * @param $class
     *
     * @return array
     */
    protected static function createPostDeserializeSubscriber($class)
    {
        return [
            'event' => 'serializer.post_deserialize',
            'method' => 'onPostDeserialize',
            'class' => $class,
            'format' => 'json',
            'priority' => 0,
        ];
    }

    /**
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        $this->data = [];

        if (!is_array($event->getData())) {
            return;
        }

        $this->data = $event->getData();
    }

    /**
     * @param ObjectEvent $event
     *
     * @return void
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        if (!is_object($object)) {
            return;
        }

        $this->populateFields($object);
    }

    /**
     * @param object $object
     *
     * @return void
     */
    protected function populateFields($object)
    {

        if (!$this->isObjectAllowed($object)) {
            return;
        }

        $ignoreFields = $this->getIgnoreFields($object);

        foreach ($this->data as $property => $value) {
            if (in_array($property, $ignoreFields)) {
                continue;
            }

            $object->getExtraFields()->set($property, $value);
        }
    }

    /**
     * Is object allowed
     *
     * @param object $object
     *
     * @return bool
     */
    protected function isObjectAllowed($object)
    {
        $config = static::$config;
        $objectClass = get_class($object);

        return ($object instanceof ExtraFieldsInterface &&
            $object->getExtraFields() instanceof CollectionInterface &&
            isset($config[$objectClass]));
    }

    /**
     * Get ignore fields for object
     *
     * @param object $object
     *
     * @return array
     */
    protected function getIgnoreFields($object)
    {
        $config = static::$config;
        $objectClass = get_class($object);

        return $config[$objectClass];
    }
}
