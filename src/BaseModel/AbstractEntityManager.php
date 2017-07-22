<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;
use BaseModel\Interfaces\IdentityMap as IdentityMapInterface;
use BaseModel\Interfaces\Thenable as ThenableInterface;
use BaseModel\Interfaces\UnitOfWork as UnitOfWorkInterface;
use SplSubject;

abstract class AbstractEntityManager implements EntityManagerInterface, \SplObserver
{

    protected $autoPersistence;
    protected $identityMap;
    protected $unitOfWork;
    protected $entitiesMementos;
    private static $_instance;

    /**
     * EntityManager constructor.
     * @param bool $autoPersistence Автосохранение состояния сущности
     * @param IdentityMapInterface|null $identityMap Карта присутствия сущности
     * @param UnitOfWorkInterface|null $unitOfWork Единица работы
     * @param \SplObjectStorage|null $entitiesMementos Карта снимков состояния сущностей
     */
    public function __construct(bool $autoPersistence = true,
                                IdentityMapInterface $identityMap = null,
                                UnitOfWorkInterface $unitOfWork = null,
                                \SplObjectStorage $entitiesMementos = null)
    {
        $this->autoPersistence = $autoPersistence;
        $this->identityMap = $identityMap ?: new IdentityMap();
        $this->unitOfWork = $unitOfWork ?: new UnitOfWork();
        $this->entitiesMementos = $entitiesMementos ?: new \SplObjectStorage();
        self::$_instance = $this;
    }

    /**
     * Ссылка на объект менеджера сущностей
     * @return EntityManagerInterface
     * @throws Exception
     */
    public static function getEntityManager(): EntityManagerInterface
    {
        if (empty(self::$_instance)) {
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
    public function findById(string $entityClass, string $id): ?EntityInterface
    {
        $entity = $this->doFindById($entityClass, $id);
        if (empty($entity)) {
            return null;
        }
        $entity = $this->identityMap->get($entity);
        $entity->attach($this);
        return $entity;
    }

    /**
     * Реализация поиска сущности по идентификатору
     * @param string $entityClass
     * @param string $id
     * @return EntityInterface|null
     */
    abstract protected function doFindById(string $entityClass, string $id): ?EntityInterface;

    /**
     * Найти сущность по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return EntityInterface|null
     */
    public function findOne(string $entityClass, array $criteria): ?EntityInterface
    {
        $entity = $this->doFindOne($entityClass, $criteria);
        if (empty($entity)) {
            return null;
        }
        $entity = $this->identityMap->get($entity);
        $entity->attach($this);
        return $entity;
    }

    /**
     * Реализация поиска сущности по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return EntityInterface|null
     */
    abstract protected function doFindOne(string $entityClass, array $criteria): ?EntityInterface;

    /**
     * Поиск сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param array $bindings
     * @return EntityInterface|null
     */
    public function findOneBySql(string $entityClass, string $sql, array $bindings = []): ?EntityInterface
    {
        $entity = $this->doFindOneBySql($entityClass, $sql, $bindings);
        if (empty($entity)) {
            return null;
        }
        $entity = $this->identityMap->get($entity);
        $entity->attach($this);
        return $entity;
    }

    /**
     * Реализация поиска сущности кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param array $bindings
     * @return EntityInterface|null
     */
    abstract protected function doFindOneBySql(string $entityClass, string $sql, array $bindings = []): ?EntityInterface;

    /**
     * Найти коллекцию сущностей по критериям
     * @param string $entityClass
     * @param array $criteria
     * @return CollectionInterface
     */
    public function findAll(string $entityClass, array $criteria): CollectionInterface
    {
        $collection = $this->doFindAll($entityClass, $criteria);
        return $collection->map(function (EntityInterface $entity) {
            $entity = $this->identityMap->get($entity);
            $entity->attach($this);
            return $entity;
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
     * @param  array $bindings
     * @return CollectionInterface
     */
    public function findAllBySql(string $entityClass, string $sql, array $bindings = []): CollectionInterface
    {
        $collection = $this->doFindAllBySql($entityClass, $sql, $bindings);
        return $collection->map(function (EntityInterface $entity) {
            $entity = $this->identityMap->get($entity);
            $entity->attach($this);
            return $entity;
        });
    }

    /**
     * Реализация поиска коллекции сущностей кастомным SQL запросом
     * @param string $entityClass
     * @param string $sql
     * @param  array $bindings
     * @return CollectionInterface
     */
    abstract protected function doFindAllBySql(string $entityClass, string $sql, array $bindings = []): CollectionInterface;

    /**
     * Поставить сущность в очередь на сохранение
     * @param EntityInterface $entity
     * @return ThenableInterface
     */
    public function save(EntityInterface $entity): ThenableInterface
    {
        return $this->unitOfWork->save($entity)
            ->then(function (EntityInterface $entity) {
                if ($this->doSave($entity)) {
                    return $entity;
                }
                throw new Exception('Возникла ошибка при сохранении сущности');
            })
            ->then(function (EntityInterface $entity) {
                $this->entitiesMementos[$entity] = $entity->createMemento();
                return $entity;
            });
    }

    /**
     * Реализация сохранения сущности
     * @param EntityInterface $entity
     * @return bool
     */
    abstract protected function doSave(EntityInterface $entity): bool;

    /**
     * Поставить сущность в очередь на удаление
     * @param EntityInterface $entity
     * @return ThenableInterface
     */
    public function remove(EntityInterface $entity): ThenableInterface
    {
        return $this->unitOfWork->remove($entity)
            ->then(function (EntityInterface $entity) {
                if ($this->doRemove($entity)) {
                    return $entity;
                }
                throw new Exception('Возникла ошибка при удалении сущности');
            })
            ->then(function (EntityInterface $entity) {
                $this->entitiesMementos->detach($entity);
                return $entity;
            });
    }

    /**
     * Удалить сущность
     * @param EntityInterface $entity
     * @return bool
     */
    abstract protected function doRemove(EntityInterface $entity): bool;

    /**
     * Сохранить состояние сущностей
     */
    public function persist()
    {
        try {
            $this->unitOfWork->commit();
        } catch (Exception $exception) {
            $this->rollBack();
            throw $exception;
        }
    }

    /**
     * Откат изменений
     */
    public function rollBack()
    {
        /** @var EntityInterface $entity */
        foreach ($this->entitiesMementos as $entity) {
            /** @var \BaseModel\Interfaces\Memento $memento */
            $memento = $this->entitiesMementos[$entity];
            $entity->setMemento($memento);
        }
        $this->unitOfWork->rollBack();
    }

    /**
     * При включеной опции autoPersistence,
     * в случае изменения состояния сущности
     * происходит добавление сущности в очередь на сохранение
     *
     * @param SplSubject $subject
     * @throws Exception
     */
    public function update(SplSubject $subject)
    {
        if (!$subject instanceof EntityInterface) {
            throw new Exception(
                'Менеджер сущностей не может наблюдать за объектами не реализующими интерфейс сущности'
            );
        }
        $flag = $subject->getFlag();
        if ($this->autoPersistence === true
            and !$this->unitOfWork->contains($subject)
            and ($flag === EntityInterface::FLAG_NEW
                or $flag === EntityInterface::FLAG_DIRTY)
        ) {
            $this->save($subject);
        }
    }
}