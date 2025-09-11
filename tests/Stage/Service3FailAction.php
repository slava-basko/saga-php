<?php

namespace Basko\Saga\Stage;

use Basko\Saga\StageInterface;
use Basko\Saga\Value\Trace;

class Service3FailAction implements StageInterface
{
    public function execute($payload)
    {
        throw new \Exception('From service 3');
    }

    public function rollback($payload)
    {
        if ($payload instanceof Trace) {
            $payload->sub(3);
            return $payload;
        }
        return $payload - 3;
    }
}