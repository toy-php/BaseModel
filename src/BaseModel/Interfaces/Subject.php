<?php

namespace BaseModel\Interfaces;

interface Subject extends \SplSubject, Object
{

    /**
     * Флаг указывающий на то, что полученый экземпляр субъекта пуст
     */
    const FLAG_EMPTY = 0x00;

    /**
     * Флаг указывающий на то, что полученый экземпляр субъекта,
     * данные которой получены из источника
     */
    const FLAG_CLEAN = 0x01;

    /**
     * Флаг указывающий на то, что полученый экземпляр субъекта,
     * данные которой отсутствуют в источнике
     */
    const FLAG_NEW = 0x02;

    /**
     * Флаг указывающий на то, что полученый экземпляр субъекта,
     * данные которой изменены и не соответствуют данным полученым из источника
     */
    const FLAG_DIRTY = 0x03;

    /**
     * Получить экземпляр субъекта
     * @param string $subjectClass
     * @return Subject
     */
    public static function create(string $subjectClass): Subject;

    /**
     * Получить флаг состояния субъекта
     * @return int
     */
    public function getFlag(): int;

    /**
     * Установить идентификатор субъекта
     * @param string $id
     */
    public function setId(string $id);

    /**
     * Получить идентификатор субъекта
     * @return string
     */
    public function getId();

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