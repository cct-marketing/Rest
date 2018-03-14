<?php

namespace CCT\Component\Rest\Tests\Fixture;

class TestModel
{
    /**
     * @var string
     */
    public $heading;

    /**
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
    }
}
