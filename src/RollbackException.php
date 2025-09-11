<?php

namespace Basko\Saga;

class RollbackException extends \Exception
{
    private $payload;

    /**
     * @param $payload
     * @param \Exception $originalException
     * @return \Basko\Saga\RollbackException
     */
    public static function create($payload, \Exception $originalException)
    {
        $exception = new RollbackException(
            'Rollback completed',
            $originalException->getCode(),
            $originalException
        );
        $exception->payload = $payload;

        return $exception;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
