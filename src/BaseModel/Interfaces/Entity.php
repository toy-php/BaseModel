<?php

namespace BaseModel\Interfaces;

interface Entity extends Subject
{
    /**
     * Получить экземпляр сущности с соответствующими данными
     * @param array $data
     * @return Entity
     */
    public function withData(array $data): Entity;

    /**
     * Получить снимок состояния сущности
     * @return Memento
     */
    public function createMemento(): Memento;

    /**
     * Вернуть состояние сущности из снимка состояния
     * @param Memento $memento
     */
    public function setMemento(Memento $memento);

    /**
     * Получить атрибуты в виде массива
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Получить измененные атрибуты в виде массива
     * @return array
     */
    public function getDirtyAttributes(): array;

    /**
     * Получить структуру сущности в виде массива
     * @return array
     */
    public function toArray(): array;
}