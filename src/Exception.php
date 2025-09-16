<?php

namespace Basko\Saga;

class Exception extends \Exception
{
    /**
     * @var mixed
     */
    private $payload;

    /**
     * @var array<\Exception>
     */
    private $rollbackExceptions = [];

    public static function create($payload, \Exception $stageException, array $rollbackExceptions = [])
    {
        $exception = new Exception(
            'Pipeline exception',
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
