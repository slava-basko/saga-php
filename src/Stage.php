<?php

namespace Basko\Saga;

abstract class Stage implements StageInterface
{
    /**
     * @param mixed $payload
     * @return mixed
     */
    abstract protected function rollback($payload);
}
