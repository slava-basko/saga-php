<?php

namespace Basko\Saga\Stage;

use Basko\Saga\StageInterface;
use Basko\Saga\Value\Trace;

class Service1Action implements StageInterface
{
    public function execute($payload)
    {
        if ($payload instanceof Trace) {
            $payload->add(1);
            return $payload;
        }
        return $payload + 1;
    }

    public function rollback($payload)
    {
        if ($payload instanceof Trace) {
            $payload->sub(1);
            return $payload;
        }
        return $payload - 1;
    }
}