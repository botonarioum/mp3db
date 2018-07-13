<?php

namespace Pipes;

use Task\Task;

class RemoveTempFilePipe extends AbstractPipe
{
    const INTRODUCE_MESSAGE = 'Remove temporary file';

    public function __invoke(Task $task): Task
    {
        $task = parent::__invoke($task);

        $this->process($task);

        return $task;
    }

    public function process(Task $task)
    {
        unlink($task->getFilePath());
    }
}
