<?php

namespace BaseModel\Interfaces;

use BaseModel\Exception;

interface ValueObject extends Object
{

    /**
     * В случае изменения состояния объекта,
     * должно выбрасываться исключение
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function __set($name, $value);

    /**
     * В случае изменения состояния объекта,
     * должно выбрасываться исключение
     * @param $name
     * @throws Exception
     */
    public function __unset($name);

}