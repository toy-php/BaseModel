<?php

namespace BaseModel\Interfaces;

interface Subject extends \SplSubject, Object
{

    /**
     * Получить флаг сущности
     * @return int
     */
    public function getFlag();

    /**
     * Установить свойство
     * При установке свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     * @param $value
     */
    public function __set($name, $value);

    /**
     * Удалить свойство
     * При удалении свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     */
    public function __unset($name);
}