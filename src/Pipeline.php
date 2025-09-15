<?php

namespace Basko\Saga;

class Pipeline extends Stage
{
    /**
     * @var array<\Basko\Saga\Stage>
     */
    private $stages = [];

    /**
     * @var array<\Basko\Saga\Stage>
     */
    private $stack = [];

    /**
     * @var array<\Exception>
     */
    private $rollbackExceptions = [];

    /**
     * @var bool
     */
    private $continueRollbackOnException = false;

    /**
     * @var bool
     */
    private $rollbackFailedStage = false;

    /**
     * @var int
     */
    private $executionDepth = 0;

    private function cleanup()
    {
        foreach ($this->stages as $stage) {
            if ($stage instanceof Pipeline) {
                $stage->cleanup();
            }
        }

        $this->stack = [];
        $this->rollbackExceptions = [];
    }

    /**
     * @param mixed $payload
     * @return mixed
     */
    protected function rollback($payload)
    {
        while ($stage = \array_pop($this->stack)) {
            if ($this->continueRollbackOnException) {
                try {
                    $payload = $stage->rollback($payload);
                } catch (\Exception $exception) {
                    $this->rollbackExceptions[] = $exception;
                }

                continue;
            }

            $payload = $stage->rollback($payload);
        }

        return $payload;
    }

    public function execute($payload)
    {
        $this->executionDepth++;
        $isRootExecution = $this->executionDepth === 1;

        try {
            foreach ($this->stages as $stage) {
                try {
                    if ($this->rollbackFailedStage) {
                        $this->stack[] = $stage;
                    }

                    if ($stage instanceof Pipeline) {
                        $stage->executionDepth = $this->executionDepth + 1;
                    }

                    $payload = $stage->execute($payload);

                    if (!$this->rollbackFailedStage) {
                        $this->stack[] = $stage;
                    }
                } catch (\Exception $stageException) {
                    $payload = $this->rollback($payload);
                    throw RollbackException::create($payload, $stageException, $this->rollbackExceptions);
                }
            }

            if ($isRootExecution) {
                $this->cleanup();
            }

            return $payload;
        } finally {
            $this->executionDepth--;
        }
    }

    /**
     * @param \Basko\Saga\Stage $stage
     * @return void
     */
    public function addStage(Stage $stage)
    {
        $this->stages[] = $stage;
    }

    /**
     * @param bool $continueRollbackOnException
     */
    public function continueRollbackOnException($continueRollbackOnException)
    {
        $this->continueRollbackOnException = $continueRollbackOnException;
    }

    /**
     * @param bool $rollbackFailedStage
     */
    public function rollbackFailedStage($rollbackFailedStage)
    {
        $this->rollbackFailedStage = $rollbackFailedStage;
    }
}
