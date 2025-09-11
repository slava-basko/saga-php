<?php

namespace Basko\Saga\TestCase;

use Basko\Saga\Pipeline;
use Basko\Saga\Stage\Service1Action;
use Basko\Saga\Stage\Service2Action;
use Basko\Saga\Stage\Service3Action;
use Basko\Saga\Stage\Service3FailAction;
use Basko\Saga\Value\Trace;
use PHPUnit\Framework\TestCase;

class PipeTest extends TestCase
{
    public function testPipe()
    {
        $pipe = new Pipeline();
        $pipe->addStage(new Service1Action());
        $pipe->addStage(new Service2Action());
        $pipe->addStage(new Service3Action());

        $result = $pipe->execute(1);

        $this->assertEquals(7, $result);
    }

    public function testPipeFail()
    {
        try {
            $pipe = new Pipeline();
            $pipe->addStage(new Service1Action());
            $pipe->addStage(new Service2Action());
            $pipe->addStage(new Service3FailAction());

            $pipe->execute(1);
        } catch (\Exception $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals('From service 3', $e->getPrevious()->getMessage());
        }
    }

    public function testPipeCheckRollback()
    {
        $trace = new Trace();

        try {
            $pipe = new Pipeline();
            $pipe->addStage(new Service1Action());
            $pipe->addStage(new Service2Action());
            $pipe->addStage(new Service3FailAction());

            $pipe->execute($trace);
        } catch (\Exception $e) {
            $this->assertEquals('Rollback completed', $e->getMessage());
            $this->assertEquals('From service 3', $e->getPrevious()->getMessage());
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
        $pipe1->addStage(new Service1Action());
        $pipe1->addStage(new Service2Action());

        $pipe2 = new Pipeline();
        $pipe2->addStage($pipe1);
        $pipe2->addStage(new Service3Action());

        $result = $pipe2->execute(1);

        $this->assertEquals(7, $result);
    }
}