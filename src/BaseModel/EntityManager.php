<?php

namespace BaseModel;

use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;
use BaseModel\Interfaces\IdentityMap as IdentityMapInterface;
use BaseModel\Interfaces\Thenable as ThenableInterface;
use BaseModel\Interfaces\UnitOfWork as UnitOfWorkInterface;

abstract class EntityManager implements EntityManagerInterface
{

    protected $identityMap;
    protected $unitOfWork;
    private static $_instance;

    public function __construct(IdentityMapInterface $identityMap = null,
                                UnitOfWorkInterface $unitOfWork = null)
    {
        $this->identityMap = $identityMap ?: new IdentityMap();
        $this->unitOfWork = $unitOfWork ?: new UnitOfWork();
        self::$_instance = $this;
    }

    /**
     * Ссылка на объект менеджера сущностей
     * @return EntityManagerInterface
     * @throws Exception
     */
    public static function getInstance(): EntityManagerInterface
    {
        if(empty(self::$_instance)){
            throw new Exception('Менеджер сущностей не инициализирован');
        }
        return self::$_instance;
    }

    /**
     * Найти сущность по идентификатору или по критериям
     * @param string $entityClass
     * @param string $id
     * @return EntityInterface|null
     */
    public function findById(string $entityClass, string $id)
    {
        $entity = $this->doFindById($entityClass, $id);
        if (empty($entity)){
            return null;
        }
        return $this->identityMap->get($entity);
    }

    /**
     * Реализация поиска сущности по идентификатору
     * @param string $entityClass
     * @param string $id
     * @return EntityInterface|null
     */
    abstract protected function doFindById(string $entityClass, string $id);

    /**
     * Найти сущность по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return EntityInterface|null
     */
    public function findOne(string $entityClass, array $criteria)
    {
        $entity = $this->doFindOne($entityClass, $criteria);
        if (empty($entity)){
            return null;
        }
        return $this->identityMap->get($entity);
    }

    /**
     * Реализация поиска сущности по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return EntityInterface|null
     */
    abstract protected function doFindOne(string $entityClass, array $criteria);

    /**
     * Поиск сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @return EntityInterface|null
     */
    public function findOneBySql(string $entityClass, string $sql)
    {
        $entity = $this->doFindOneBySql($entityClass, $sql);
        if (empty($entity)){
            return null;
        }
        return $this->identityMap->get($entity);
    }

    /**
     * Реализация поиска сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @return EntityInterface|null
     */
    abstract protected function doFindOneBySql(string $entityClass, string $sql);

    /**
     * Найти коллекцию сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return CollectionInterface
     */
    public function findAll(string $entityClass, array $criteria): CollectionInterface
    {
        $collection = $this->doFindAll($entityClass, $criteria);
        return $collection->map(function (EntityInterface $entity){
            return $this->identityMap->get($entity);
        });
    }

    /**
     * Реализация поиска коллекции сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return CollectionInterface
     */
    abstract protected function doFindAll(string $entityClass, array $criteria): CollectionInterface;

    /**
     * Найти коллекцию сущностей кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @return CollectionInterface
     */
    public function findAllBySql(string $entityClass, string $sql): CollectionInterface
    {
        $collection = $this->doFindAllBySql($entityClass, $sql);
        return $collection->map(function (EntityInterface $entity){
            return $this->identityMap->get($entity);
        });
    }

    /**
     * Реализация поиска коллекции сущностей кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @return CollectionInterface
     */
    abstract protected function doFindAllBySql(string $entityClass, string $sql): CollectionInterface;

    /**
     * Поставить сущность в очередь на сохранение
     * @param EntityInterface $entity
     * @return ThenableInterface
     */
    public function save(EntityInterface $entity): ThenableInterface
    {
        return $this->unitOfWork->save($entity)
            ->then(function (EntityInterface $entity){
                return $this->doSave($entity);
            });
    }

    /**
     * Реализация сохранения сущности
     * @param EntityInterface $entity
     * @return bool
     */
    abstract protected function doSave(EntityInterface $entity):bool ;

    /**
     * Поставить сущность в очередь на удаление
     * @param EntityInterface $entity
     * @return ThenableInterface
     */
    public function remove(EntityInterface $entity): ThenableInterface
    {
        return $this->unitOfWork->remove($entity)
            ->then(function (EntityInterface $entity){
                return $this->doRemove($entity);
            });
    }

    /**
     * Удалить сущность
     * @param EntityInterface $entity
     * @return bool
     */
    abstract protected function doRemove(EntityInterface $entity): bool;

    /**
     * Закрепление изменений
     */
    public function commit()
    {
        $this->unitOfWork->commit();
    }

    /**
     * Откат изменений
     */
    public function rollBack()
    {
        $this->unitOfWork->rollBack();
    }
}