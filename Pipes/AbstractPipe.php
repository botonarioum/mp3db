<?php

namespace Pipes;

use Task\Task;

abstract class AbstractPipe implements PipeInterface
{
    const INTRODUCE_MESSAGE = 'Example introduce message';

    public function __invoke(Task $task): Task
    {
        $this->doIntroduce();

        return $task;
    }

    public function process(Task $task)
    {
        // TODO: Implement process() method.
    }

    protected function doIntroduce(): void
    {
        var_dump($this->getIntroduceMessage());
    }

    private function getIntroduceMessage(): string
    {
        return $this::INTRODUCE_MESSAGE;
    }
}