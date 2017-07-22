<?php

namespace BaseModel\Interfaces;

interface Memento
{

    /**
     * Получить состояние
     * @return mixed
     */
    public function getState();
}