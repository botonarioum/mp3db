<?php

interface Message
{
    public function getPayload(): array;
}

interface Queue
{
    public function addMessage(Task $task): void;
}
