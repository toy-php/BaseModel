<?php

namespace BaseModel\Interfaces;

interface EntityManager
{

    /**
     * Ссылка на объект менеджера сущностей
     * @return EntityManager
     */
    public static function getEntityManager(): EntityManager;

    /**
     * Найти сущность по идентификатору
     * @param string $entityClass
     * @param string $id
     * @return Entity|null
     */
    public function findById(string $entityClass, string $id);

    /**
     * Найти сущность по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return Entity|null
     */
    public function findOne(string $entityClass, array $criteria);

    /**
     * Поиск сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param array $bindings
     * @return Entity|null
     */
    public function findOneBySql(string $entityClass, string $sql, array $bindings = []): ?Entity;

    /**
     * Найти коллекцию сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return Collection
     */
    public function findAll(string $entityClass, array $criteria): Collection;

    /**
     * Найти коллекцию сущностей кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @return Collection
     */
    public function findAllBySql(string $entityClass, string $sql): Collection;

    /**
     * Получить количество сущностей удовлетворяющих критерии
     * @param string $entityClass
     * @param array $criteria
     * @return int
     */
    public function count(string $entityClass, array $criteria): int;

    /**
     * Поставить сущность в очередь на сохранение
     * @param Entity $entity
     * @return Thenable
     */
    public function save(Entity $entity): Thenable;

    /**
     * Поставить сущность в очередь на удаление
     * @param Entity $entity
     * @return Thenable
     */
    public function remove(Entity $entity): Thenable;

    /**
     * Закрепление изменений
     */
    public function persist();

    /**
     * Откат изменений
     */
    public function rollBack();
}