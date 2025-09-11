<?php

namespace Basko\Saga\Value;

class Trace
{
    private $value = 1;

    private $log = [];

    public function add($value)
    {
        $this->log[] = 'add' . $value;
        $this->value += $value;
    }

    public function sub($value)
    {
        $this->log[] = 'sub' . $value;
        $this->value -= $value;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getValue()
    {
        return $this->value;
    }
}