<?php

namespace BaseModel;

use BaseModel\Interfaces\ValueObject as ValueObjectInterface;

class ValueObject extends Object implements ValueObjectInterface
{
    /**
     * В случае изменения состояния объекта,
     * должно выбрасываться исключение
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        throw new Exception('Объект является неизменяемым');
    }

    /**
     * В случае изменения состояния объекта,
     * должно выбрасываться исключение
     * @param $name
     * @throws Exception
     */
    public function __unset($name)
    {
        throw new Exception('Объект является неизменяемым');
    }
}