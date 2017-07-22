<?php

namespace BaseModel\Interfaces;

interface Mapper
{

    /**
     * Функция ленивой инициализации преобразователя
     * функция в качестве аргумента принимает контейнер BaseModel\Interfaces\Container
     * для получения сервисных зависимостей преобразователем. Пример:
     *
     * <pre>
     *   return function (ContainerInterface $container) use ($table, $entityClass, $primaryKey) {
     *       list($adapter) = $container->required([
     *           'adapter' => AdapterInterface::class
     *       ]);
     *       return new self($adapter, $table, $entityClass, $primaryKey);
     *   };
     * </pre>
     *
     * @param string $table
     * @param string $entityClass
     * @param string $primaryKey
     * @return callable
     */
    public static function lazyBuild(string $table, string $entityClass, string $primaryKey = 'id'): callable;

    /**
     * Реализация поиска сущности по идентификатору
     * @param string $id
     * @return Entity|null
     */
    public function findById(string $id): ?Entity;

    /**
     * Реализация поиска сущности по критериям
     * @param array $criteria
     * @return Entity|null
     */
    public function findOne(array $criteria): ?Entity;

    /**
     * Реализация поиска сущности кастомным SQL запросом
     * @param string $sql
     * @param array $bindings
     * @return Entity|null
     */
    public function findOneBySql(string $sql, array $bindings =[]): ?Entity;

    /**
     * Реализация поиска коллекции сущностей по критериям
     * @param array $criteria
     * @return Collection
     */
    public function findAll(array $criteria): Collection;

    /**
     * Реализация поиска коллекции сущностей кастомным SQL запросом
     * @param string $sql
     * @param array $bindings
     * @return Collection
     */
    public function findAllBySql(string $sql, array $bindings =[]): Collection;

    /**
     * Реализация сохранения сущности
     * @param Entity $entity
     * @return bool
     */
    public function save(Entity $entity): bool;

    /**
     * Удалить сущность
     * @param Entity $entity
     * @return bool
     */
    public function remove(Entity $entity): bool;

    /**
     * Получить количество сущностей удовлетворяющих критерии
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria): int;
}