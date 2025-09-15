<?php

namespace Basko\Saga;

class RollbackException extends \Exception
{
    private $payload;

    private $rollbackExceptions = [];

    public static function create($payload, \Exception $stageException, array $rollbackExceptions = [])
    {
        $exception = new RollbackException(
            'Rollback completed',
            $stageException->getCode(),
            $stageException
        );

        $exception->payload = $payload;
        $exception->rollbackExceptions = $rollbackExceptions;

        return $exception;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return array
     */
    public function getRollbackExceptions()
    {
        return $this->rollbackExceptions;
    }
}
