<?php

namespace Basko\Saga\Stage;

use Basko\Saga\StageInterface;
use Basko\Saga\Value\Trace;

class StageN implements StageInterface
{
    private $n;
    private $exceptionOnExecute;
    private $exceptionOnRollback;

    public function __construct($n, $exceptionOnExecute = false, $exceptionOnRollback = false)
    {
        $this->n = $n;
        $this->exceptionOnExecute = $exceptionOnExecute;
        $this->exceptionOnRollback = $exceptionOnRollback;
    }

    public function execute($payload)
    {
        if ($this->exceptionOnExecute) {
            throw new \Exception("Execute error on stage {$this->n}");
        }
        if ($payload instanceof Trace) {
            $payload->add($this->n);
            return $payload;
        }
        return $payload + $this->n;
    }

    public function rollback($payload)
    {
        if ($this->exceptionOnRollback) {
            throw new \Exception("Rollback error on stage {$this->n}");
        }
        if ($payload instanceof Trace) {
            $payload->sub($this->n);
            return $payload;
        }
        return $payload - $this->n;
    }
}
