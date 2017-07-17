<?php

namespace BaseModel\Interfaces;

interface EntityManager
{

    /**
     * Найти сущность по идентификатору или по критериям
     * @param string $entityClass
     * @param $condition
     * @return Entity|null
     */
    public function find(string $entityClass, $condition);

    /**
     * Найти коллекция сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return Collection
     */
    public function findAll(string $entityClass, array $criteria): Collection;

    /**
     * Сохранить сущность
     * @param Entity $entity
     * @return Thenable
     */
    public function save(Entity $entity): Thenable;

    /**
     * Удалить сущность
     * @param Entity $entity
     * @return Thenable
     */
    public function remove(Entity $entity): Thenable;

    /**
     * Закрепление изменений
     */
    public function commit();

    /**
     * Откат изменений
     */
    public function rollBack();
}