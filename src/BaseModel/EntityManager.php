<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\IdentityMap as IdentityMapInterface;
use BaseModel\Interfaces\UnitOfWork as UnitOfWorkInterface;
use BaseModel\Interfaces\MappersMap as MappersMapInterface;

class EntityManager extends AbstractEntityManager
{

    protected $mappersMap;

    public function __construct(MappersMapInterface $mappersMap,
                                bool $autoPersistence = true,
                                IdentityMapInterface $identityMap = null,
                                UnitOfWorkInterface $unitOfWork = null,
                                \SplObjectStorage $entitiesMementos = null)
    {
        $this->mappersMap = $mappersMap;
        parent::__construct($autoPersistence, $identityMap, $unitOfWork, $entitiesMementos);
    }

    /**
     * Реализация поиска сущности по идентификатору
     * @param string $entityClass
     * @param string $id
     * @return EntityInterface|null
     */
    protected function doFindById(string $entityClass, string $id): ?EntityInterface
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->findById($id);
    }

    /**
     * Реализация поиска сущности по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return EntityInterface|null
     */
    protected function doFindOne(string $entityClass, array $criteria): ?EntityInterface
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->findOne($criteria);
    }

    /**
     * Реализация поиска сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param array $bindings
     * @return EntityInterface|null
     */
    protected function doFindOneBySql(string $entityClass, string $sql, array $bindings = []): ?EntityInterface
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->findOneBySql($sql, $bindings);
    }

    /**
     * Реализация поиска коллекции сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return CollectionInterface
     */
    protected function doFindAll(string $entityClass, array $criteria): CollectionInterface
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->findAll($criteria);
    }

    /**
     * Реализация поиска коллекции сущностей кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param array $bindings
     * @return CollectionInterface
     */
    protected function doFindAllBySql(string $entityClass, string $sql, array $bindings = []): CollectionInterface
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->findAllBySql($sql, $bindings);
    }

    /**
     * Реализация сохранения сущности
     * @param EntityInterface $entity
     * @return bool
     */
    protected function doSave(EntityInterface $entity): bool
    {
        $mapper = $this->mappersMap->loadMapper(get_class($entity));
        return $mapper->save($entity);
    }

    /**
     * Удалить сущность
     * @param EntityInterface $entity
     * @return bool
     */
    protected function doRemove(EntityInterface $entity): bool
    {
        $mapper = $this->mappersMap->loadMapper(get_class($entity));
        return $mapper->remove($entity);
    }

    /**
     * Получить количество сущностей удовлетворяющих критерии
     * @param string $entityClass
     * @param array $criteria
     * @return int
     */
    public function count(string $entityClass, array $criteria): int
    {
        $mapper = $this->mappersMap->loadMapper($entityClass);
        return $mapper->count($criteria);
    }
}