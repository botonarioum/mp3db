<?php

interface QueueInterface
{
    public function addMessage(Task $task): void;
}
