<?php

namespace BaseModel\Interfaces;

interface Collection extends \ArrayAccess, \IteratorAggregate, \Countable
{

    /**
     * Получить тип сущности которую содержит коллекция
     * @return string
     */
    public function getType(): string;

    /**
     * Получить экземпляр коллекции с массивом сущностей сущностью
     * @param string $entityClass
     * @param Entity[] $entities
     * @return Collection
     */
    public static function withEntities(string $entityClass, array $entities) : Collection;

    /**
     * Получить экземпляр коллекции с новой сущностью
     * @param Entity $entity
     * @return Collection
     */
    public function withEntity(Entity $entity) : Collection;

    /**
     * Получить экземпляр коллекции без указанной сущности
     * @param Entity $entity
     * @return Collection
     */
    public function withoutEntity(Entity $entity) : Collection;

    /**
     * Получить сущность
     * @param mixed $offset
     * @return Entity
     */
    public function offsetGet($offset) : Entity;

    /**
     * Коллекции должны быть иммутабельны,
     * метод необходимо заглушить
     * @param mixed $offset
     * @param Entity $value
     * @throws \Throwable
     */
    public function offsetSet($offset, $value);

    /**
     * Наличие сущности
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) : bool;

    /**
     * Коллекции должны быть иммутабельны,
     * метод необходимо заглушить
     * @param mixed $offset
     * @return void
     * @throws \Throwable
     */
    public function offsetUnset($offset);

    /**
     * Очистить коллекцию
     */
    public function clear();

    /**
     * Фильтрация коллекции.
     * Обходит каждый объект коллекции,
     * передавая его в callback-функцию.
     * Если callback-функция возвращает true,
     * данный объект из текущей коллекции попадает в результирующую коллекцию.
     * @param callable $function
     * @return Collection
     */
    public function filter(callable $function): Collection;

    /**
     * Перебор всех объектов коллекции.
     * Возвращает новую коллекцию,
     * содержащую объекты после их обработки callback-функцией.
     * @param callable $function
     * @return Collection
     */
    public function map(callable $function): Collection;

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $function, $initial = null);

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function);

    /**
     * Сортировать по полю
     * @param string $fieldName
     * @param string $direction
     * @return Collection
     */
    public function sortByField(string $fieldName, string $direction = 'asc'): Collection;

    /**
     * Сортировка коллекции по ключам
     * @param string $direction
     * @return Collection
     */
    public function keySort(string $direction = 'asc'): Collection;
    /**
     * Поиск объекта по значению свойства
     * @param $property
     * @param $value
     * @return Entity|null
     */
    public function search($property, $value);

    /**
     * Преобразовать коллекцию в массив
     * @return array
     */
    public function toArray(): array;
}