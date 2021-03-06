<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\Thenable as ThenableInterface;
use BaseModel\Interfaces\UnitOfWork as UnitOfWorkInterface;

class UnitOfWork implements UnitOfWorkInterface
{

    /**
     * @var \SplObjectStorage
     */
    protected $entities;

    public function __construct(\SplObjectStorage $entities = null)
    {
        $this->entities = $entities ?: new \SplObjectStorage();
    }

    /**
     * Наличие сущности в карте состояний
     * @param EntityInterface $entity
     * @return bool
     */
    public function contains(EntityInterface $entity):bool
    {
        return $this->entities->contains($entity);
    }

    /**
     * Исключить сущность из карты состояний
     * @param EntityInterface $entity
     */
    public function detach(EntityInterface $entity)
    {
        $this->entities->detach($entity);
    }

    /**
     * Поставить сущность в очередь на сохранение
     * @param EntityInterface $entity
     * @return ThenableInterface
     * @throws Exception
     */
    public function save(EntityInterface $entity): ThenableInterface
    {
        if ($this->contains($entity)) {
            throw new Exception('Сущность уже добавлена в очередь на сохранение');
        }
        $flag = $entity->getFlag();
        switch ($flag) {
            case EntityInterface::FLAG_DIRTY:
            case EntityInterface::FLAG_NEW:
                $then = new Thenable(function (EntityInterface $entity) {
                    return $entity;
                });
                $this->entities->attach($entity, $then);
                return $then;
            case EntityInterface::FLAG_EMPTY:
                throw new Exception('Сущность имеет флаг пустой и не может быть сохранена');
            default:
                throw new Exception('Не определен флаг сущности');
        }
    }

    /**
     * Поставить сущность в очередь на удаление
     * @param EntityInterface $entity
     * @return ThenableInterface
     * @throws Exception
     */
    public function remove(EntityInterface $entity): ThenableInterface
    {
        if ($this->contains($entity)) {
            throw new Exception('Сущность уже добавлена в карту состояний');
        }
        $then = new Thenable(function (EntityInterface $entity) {
            return $entity;
        });
        $this->entities->attach($entity, $then);
        return $then;
    }

    /**
     * Сохранение изменения
     */
    public function commit()
    {
        /** @var EntityInterface $entity */
        foreach ($this->entities as $entity) {
            $this->entities[$entity]($entity);
        }
        $this->entities->removeAll($this->entities);
    }

    /**
     * Очистить карту состояний
     */
    public function rollBack()
    {
        $this->entities->removeAll($this->entities);
    }
}