<?php

namespace Basko\Saga\Stage;

use Basko\Saga\StageInterface;
use Basko\Saga\Value\Trace;

class Service2Action implements StageInterface
{
    public function execute($payload)
    {
        if ($payload instanceof Trace) {
            $payload->add(2);
            return $payload;
        }
        return $payload + 2;
    }

    public function rollback($payload)
    {
        if ($payload instanceof Trace) {
            $payload->sub(2);
            return $payload;
        }
        return $payload - 2;
    }
}