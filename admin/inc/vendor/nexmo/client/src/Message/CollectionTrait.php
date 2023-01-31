<?php
namespace Nexmo\Message;

trait CollectionTrait
{
    protected $index = NULL;

    public function setIndex($index)
    {
        $this->index = (int) $index;
    }
}