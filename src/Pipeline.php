<?php

namespace Basko\Saga;

class Pipeline implements StageInterface
{
    /**
     * @var array<\Basko\Saga\StageInterface>
     */
    private $stages = [];

    /**
     * @var array<\Basko\Saga\StageInterface>
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

    /**
     * @return void
     */
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
     * @inheritDoc
     */
    public function rollback($payload)
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

    /**
     * @inheritDoc
     * @throws \Basko\Saga\Exception
     */
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
                    throw Exception::create($payload, $stageException, $this->rollbackExceptions);
                }
            }

            return $payload;
        } finally {
            if ($isRootExecution) {
                $this->cleanup();
            }

            $this->executionDepth--;
        }
    }

    /**
     * @param \Basko\Saga\StageInterface $stage
     * @return void
     */
    public function addStage(StageInterface $stage)
    {
        $this->stages[] = $stage;
    }

    /**
     * Execute: A -> B -> C
     * Rollback: C -> B -> A
     * Do we need to run A::rollback() in case of Exception in B::rollback()?
     *
     * @param bool $continueRollbackOnException
     */
    public function continueRollbackOnException($continueRollbackOnException)
    {
        $this->continueRollbackOnException = (bool)$continueRollbackOnException;
    }

    /**
     * Execute: A -> B -> C
     * Rollback C -> B -> A
     * Do we need to run B::rollback() in case of Exception in B::execute()?
     *
     * @param bool $rollbackFailedStage
     */
    public function rollbackFailedStage($rollbackFailedStage)
    {
        $this->rollbackFailedStage = (bool)$rollbackFailedStage;
    }
}
