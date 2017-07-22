<?php

namespace BaseModel\Interfaces;

interface Container extends \ArrayAccess
{

    /**
     * Проверка и получение необходимых значений
     * @param array $params
     * @return array
     */
    public function required(array $params);

    /**
     * Проверка наличия значения в контейнере по ключу
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool;

    /**
     * Получить значение контейнера по ключу
     * если значением является исполняемая фунция,
     * то возвращается результат её выполнения
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset);

    /**
     * Добавить значение в контейнер
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value);

    /**
     * Исключить значение из контейнера по ключу
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset);

    /**
     * Получить сырые данные
     * @param $name
     * @return mixed
     */
    public function raw($name);

    /**
     * Объявить функцию фабрикой
     * @param $callable
     * @return void
     */
    public function factory($callable);
}