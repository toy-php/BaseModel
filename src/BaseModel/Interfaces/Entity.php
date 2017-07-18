<?php

namespace BaseModel\Interfaces;

interface Entity extends Subject
{

    /**
     * Флаг указывающий на то, что полученый экземпляр сущности пуст
     */
    const FLAG_EMPTY = 0x00;

    /**
     * Флаг указывающий на то, что полученый экземпляр сущности,
     * данные которой получены из источника
     */
    const FLAG_CLEAN = 0x01;

    /**
     * Флаг указывающий на то, что полученый экземпляр сущности,
     * данные которой отсутствуют в источнике
     */
    const FLAG_NEW = 0x02;

    /**
     * Флаг указывающий на то, что полученый экземпляр сущности,
     * данные которой изменены и не соответствуют данным полученым из источника
     */
    const FLAG_DIRTY = 0x03;

    /**
     * Получить идентификатор сущности
     * @return string
     */
    public function getId();

    /**
     * Получить экземпляр сущности с соответствующим идентификатором
     * @param string $id
     * @return Entity
     */
    public function withId(string $id): Entity;

    /**
     * Откат изменений
     */
    public function rollBack();

    /**
     * Получить структуру в виде массива
     * @return array
     */
    public function toArray(): array;
}