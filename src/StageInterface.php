<?php

namespace Basko\Saga;

interface StageInterface
{
    public function execute($payload);

    public function rollback($payload);
}
