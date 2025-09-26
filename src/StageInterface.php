<?php

namespace Basko\Saga;

interface StageInterface
{
    /**
     * @param mixed $payload
     * @return mixed
     * @throws \Basko\Saga\Exception
     */
    public function execute($payload);

    /**
     * INTERNAL: This method should only be called by Pipeline during rollback process. Manual invocation may lead to
     * inconsistent state.
     *
     * @param mixed $payload
     * @return mixed
     * @throws \Exception
     */
    public function rollback($payload);
}
