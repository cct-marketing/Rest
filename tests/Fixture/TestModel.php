<?php

namespace CCT\Component\Rest\Tests\Fixture;

class TestModel
{
    /**
     * @var string
     */
    public $heading;

    /**
     * @var string
     */
    public $body;

    /**
     * @return string
     */
    public function getHeading(): ?string
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     */
    public function setHeading(string $heading)
    {
        $this->heading = $heading;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }


}
