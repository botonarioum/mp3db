<?php

namespace Pipes;

use Task\Task;

interface PipeInterface
{
    public function __invoke(Task $task): Task;

    public function process(Task $task);
}