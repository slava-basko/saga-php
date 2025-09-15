<?php

namespace Basko\Saga\TestCase;

use Basko\Saga\Pipeline;
use Basko\Saga\RollbackException;
use Basko\Saga\Stage\StageN;
use Basko\Saga\Value\Trace;
use PHPUnit\Framework\TestCase;

class PipeTest extends TestCase
{
    public function testPipe()
    {
        $pipe = new Pipeline();
        $pipe->addStage(new StageN(1));
        $pipe->addStage(new StageN(2));
        $pipe->addStage(new StageN(3));

        $result = $pipe->execute(1);

        $this->assertEquals(7, $result);
    }

    public function testPipeFail()
    {
        try {
            $pipe = new Pipeline();
            $pipe->addStage(new StageN(1));
            $pipe->addStage(new StageN(2));
            $pipe->addStage(new StageN(3, true));

            $pipe->execute(1);
        } catch (\Exception $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals('Execute error on stage 3', $e->getPrevious()->getMessage());
        }
    }

    public function testPipeCheckRollback()
    {
        $trace = new Trace();

        try {
            $pipe = new Pipeline();
            $pipe->addStage(new StageN(1));
            $pipe->addStage(new StageN(2));
            $pipe->addStage(new StageN(3, true));

            $pipe->execute($trace);
        } catch (\Exception $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals('Execute error on stage 3', $e->getPrevious()->getMessage());
            $this->assertEquals(1, $trace->getValue());
            $this->assertEquals([
                'add1',
                'add2',
                'sub2',
                'sub1',
            ], $trace->getLog());
        }
    }

    public function testReusablePipe()
    {
        $pipe1 = new Pipeline();
        $pipe1->addStage(new StageN(1));
        $pipe1->addStage(new StageN(2));

        $pipe2 = new Pipeline();
        $pipe2->addStage($pipe1);
        $pipe2->addStage(new StageN(3));

        $result = $pipe2->execute(1);

        $this->assertEquals(7, $result);
    }

    public function testReusablePipeRollback()
    {
        try {
            $pipe1 = new Pipeline();
            $pipe1->addStage(new StageN(1));
            $pipe1->addStage(new StageN(2));

            $pipe2 = new Pipeline();
            $pipe2->addStage($pipe1);
            $pipe2->addStage(new StageN(3, true));

            $pipe2->execute(1);
        } catch (RollbackException $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals('Execute error on stage 3', $e->getPrevious()->getMessage());
            $this->assertEquals(1, $e->getPayload());
        }
    }

    public function testRollbackFailedStage()
    {
        $pipe = new Pipeline();
        $pipe->addStage(new StageN(3, true));

        try {
            $pipe->execute(7);
        } catch (RollbackException $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals(7, $e->getPayload());
        }
    }

    public function testStageAndRollbackFailed()
    {
        $pipe = new Pipeline();
        $pipe->rollbackFailedStage(true);
        $pipe->continueRollbackOnException(true);
        $pipe->addStage(new StageN(3, true, true));

        try {
            $pipe->execute(7);
        } catch (RollbackException $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals(7, $e->getPayload());

            $rollbackExceptions = $e->getRollbackExceptions();
            $this->assertEquals(1, count($rollbackExceptions));
            $this->assertEquals('Rollback error on stage 3', $rollbackExceptions[0]->getMessage());
        }
    }

    public function testStageAndRollbackFailedButContinue()
    {
        $pipe = new Pipeline();
        $pipe->rollbackFailedStage(true);
        $pipe->continueRollbackOnException(true);
        $pipe->addStage(new StageN(3, false, true));
        $pipe->addStage(new StageN(4, true, true));

        try {
            $pipe->execute(7);
        } catch (RollbackException $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals(10, $e->getPayload());

            $rollbackExceptions = $e->getRollbackExceptions();
            $this->assertEquals(2, count($rollbackExceptions));
            $this->assertEquals('Rollback error on stage 4', $rollbackExceptions[0]->getMessage());
            $this->assertEquals('Rollback error on stage 3', $rollbackExceptions[1]->getMessage());
        }
    }
}