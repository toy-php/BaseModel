<?php

declare(strict_types=1);

namespace BaseModel;

class Memento
{

    protected $state;

    /**
     * Memento constructor.
     * @param $state
     */
    public function __construct($state)
    {
        $this->state = $state;
    }

    /**
     * Получить состояние
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

}