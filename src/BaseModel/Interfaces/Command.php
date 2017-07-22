<?php

namespace BaseModel\Interfaces;


interface Command
{

    /**
     * Выполнение комманды над агрегатом
     * @return bool
     */
    public function execute(): bool;
}