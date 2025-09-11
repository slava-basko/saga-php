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
     * @param \Basko\Saga\StageInterface $stage
     * @return void
     */
    public function addStage(StageInterface $stage)
    {
        if ($stage instanceof Pipeline) {
            $this->stages = \array_merge($this->stages, $stage->stages);
        } else {
            $this->stages[] = $stage;
        }
    }

    /**
     * @param mixed $payload
     * @return mixed
     * @throws \Exception
     */
    public function execute($payload)
    {
        foreach ($this->stages as $stage) {
            try {
                $payload = $stage->execute($payload);
                $this->stack[] = $stage;
            } catch (\Exception $exception) {
                $payload = $this->rollback($payload);
                throw RollbackException::create($payload, $exception);
            }
        }

        $this->stack = [];

        return $payload;
    }

    /**
     * @param mixed $payload
     * @return mixed
     */
    public function rollback($payload)
    {
        while ($stage = \array_pop($this->stack)) {
            $payload = $stage->rollback($payload);
        }

        return $payload;
    }
}
