<?php

namespace BaseModel;

use BaseModel\Interfaces\Object as ObjectInterface;

class Object implements ObjectInterface
{

    /**
     * Установить свойство
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter) or is_callable([$this, $setter])) {
            $this->$setter($value);
            return;
        }
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter) or is_callable([$this, $getter])) {
            throw new Exception('Свойство доступно только для чтения');
        }
        throw new Exception('Свойство не доступно');
    }

    /**
     * Получить свойство
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter) or is_callable([$this, $getter])) {
            return $this->$getter();
        }
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter) or is_callable([$this, $setter])) {
            throw new Exception('Свойство доступно только для записи');
        }
        throw new Exception('Свойство не доступно');
    }

    /**
     * Проверить наличие свойства
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        $getter = 'get' . $name;
        $getterExists = method_exists($this, $getter) or is_callable([$this, $getter]);
        return $getterExists and $this->$getter() !== null;
    }

    /**
     * Удалить свойство
     * @param $name
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter) or is_callable([$this, $setter])) {
            $this->$setter(null);
        }
    }

    /**
     * Генерация исключения в случае отсутствия вызываемого метода
     * @param $method
     * @param $arguments
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        throw new Exception(sprintf('Объект не имеет метода "%s" ', $method));
    }
}