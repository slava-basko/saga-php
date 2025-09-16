<?php

namespace Basko\Saga;

interface StageInterface
{
    /**
     * @param mixed $payload
     * @return mixed
     */
    public function execute($payload);

    /**
     * INTERNAL: This method should only be called by Pipeline during rollback process. Manual invocation may lead to
     * inconsistent state.
     *
     * @param mixed $payload
     * @return mixed
     */
    public function rollback($payload);
}
