<?php

namespace BaseModel\Interfaces;

interface Object
{

    /**
     * Установить свойство
     * @param $name
     * @param $value
     */
    public function __set($name, $value);

    /**
     * Получить свойство
     * @param $name
     * @return mixed
     */
    public function __get($name);

    /**
     * Проверить наличие свойства
     * @param $name
     * @return bool
     */
    public function __isset($name);

    /**
     * Удалить свойство
     * @param $name
     */
    public function __unset($name);

}
