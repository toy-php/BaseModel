<?php

namespace BaseModel;

use BaseModel\Interfaces\Subject as SubjectInterface;
use SplObserver;

class Subject extends Object implements SubjectInterface
{

    /**
     * Флаг субъекта
     * @var int
     */
    private $_flag;

    /**
     * Массив наблюдателей за субъектом
     * @var \SplObjectStorage
     */
    private $_observers;

    public function __construct()
    {
        $this->_observers = new \SplObjectStorage();
    }

    public function __clone()
    {
        $this->_observers = new \SplObjectStorage();
    }

    /**
     * Установить флаг субъекта
     * @param int $flag
     */
    protected function setFlag(int $flag)
    {
        $this->_flag = $flag;
        $this->notify();
    }

    /**
     * Получить флаг субъекта
     * @return int
     */
    public function getFlag(): int
    {
        return $this->_flag;
    }

    /**
     * Установить свойство
     * При установке свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        $this->notify();
    }

    /**
     * Удалить свойство
     * При удалении свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     */
    public function __unset($name)
    {
        parent::__unset($name);
        $this->notify();
    }

    /**
     * Добавить наблюдателя за субъектом
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        $this->_observers->attach($observer);
    }

    /**
     * Исключить наблюдателя за субъектом
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        $this->_observers->detach($observer);
    }

    /**
     * Оповестить наблюдателей об изменении состояния
     */
    public function notify()
    {
        /** @var SplObserver $observer */
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}