<?php

namespace BaseModel;

use BaseModel\Interfaces\Adapter as AdapterInterface;
use BaseModel\Interfaces\Container as ContainerInterface;
use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\Mapper as MapperInterface;

class Mapper implements MapperInterface
{

    protected static $adapterName = 'adapter';
    protected $adapter;
    protected $tableName;
    protected $entityClass;
    protected $primaryKey;

    public function __construct(AdapterInterface $adapter, string $tableName, string $entityClass, string $primaryKey = 'id')
    {
        $this->adapter = $adapter;
        $this->tableName = $tableName;
        $this->entityClass = $entityClass;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Получить коллекцию сущностей
     * @param array $entities
     * @return CollectionInterface
     */
    protected function buildCollection(array $entities)
    {
        return Collection::withEntities($this->entityClass, $entities);
    }

    /**
     * Получить сущность
     * @param array $data
     * @return EntityInterface
     */
    protected function buildEntity(array $data)
    {
        /**
         * @var EntityInterface $entityClass
         * @var EntityInterface $entity
         */
        $entityClass = $this->entityClass;
        $entity = $entityClass::create($this->entityClass);
        if (isset($data[$this->primaryKey])) {
            $entity->setId($data[$this->primaryKey]);
        }
        return $entity->withData($data);
    }

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
    public static function lazyBuild(string $table, string $entityClass, string $primaryKey = 'id'): callable
    {
        return function (ContainerInterface $container) use ($table, $entityClass, $primaryKey) {
            list($adapter) = $container->required([
                static::$adapterName => AdapterInterface::class
            ]);
            return new self($adapter, $table, $entityClass, $primaryKey);
        };
    }

    /**
     * Реализация поиска сущности по идентификатору
     * @param string $id
     * @return EntityInterface|null
     */
    public function findById(string $id): ?EntityInterface
    {
        return $this->findOne([
            'WHERE' => [
                $this->primaryKey => $id
            ]
        ]);
    }

    /**
     * Реализация поиска сущности по критериям
     * @param array $criteria
     * @return EntityInterface|null
     */
    public function findOne(array $criteria): ?EntityInterface
    {
        $data = $this->adapter->select($this->tableName, $criteria)
            ->fetch(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            return null;
        }
        return $this->buildEntity($data);
    }

    /**
     * Реализация поиска сущности кастомным SQL запросом
     * @param string $sql
     * @param array $bindings
     * @return EntityInterface|null
     */
    public function findOneBySql(string $sql, array $bindings = []): ?EntityInterface
    {
        $data = $this->adapter->sql($sql, $bindings)
            ->fetch(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            return null;
        }
        return $this->buildEntity($data);
    }

    /**
     * Реализация поиска коллекции сущностей по критериям
     * @param array $criteria
     * @return CollectionInterface
     */
    public function findAll(array $criteria): CollectionInterface
    {
        $rows = $this->adapter->select($this->tableName, $criteria)
            ->fetchAll(\PDO::FETCH_ASSOC);
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->buildEntity($row);
        }
        return $this->buildCollection($entities);
    }

    /**
     * Реализация поиска коллекции сущностей кастомным SQL запросом
     * @param string $sql
     * @param array $bindings
     * @return CollectionInterface
     */
    public function findAllBySql(string $sql, array $bindings = []): CollectionInterface
    {
        $rows = $this->adapter->sql($sql, $bindings)
            ->fetchAll(\PDO::FETCH_ASSOC);
        $entities = [];
        foreach ($rows as $row) {
            $entities[] = $this->buildEntity($row);
        }
        return $this->buildCollection($entities);
    }

    /**
     * Реализация сохранения сущности
     * @param EntityInterface $entity
     * @return bool
     */
    public function save(EntityInterface $entity): bool
    {
        $flag = $entity->getFlag();
        if ($flag === EntityInterface::FLAG_NEW) {
            $id = $this->adapter->insert(
                $this->tableName,
                $entity->getAttributes()
            );
            $entity->setId($id);
        }
        if ($flag === EntityInterface::FLAG_DIRTY) {
            $rowCount = $this->adapter->update(
                $this->tableName,
                $entity->getDirtyAttributes(),
                [
                    $this->primaryKey => $entity->getId()
                ]
            );
            return !empty($rowCount);
        }
        return false;
    }

    /**
     * Удалить сущность
     * @param EntityInterface $entity
     * @return bool
     */
    public function remove(EntityInterface $entity): bool
    {
        $id = $entity->getId();
        if(empty($id)){
            return false;
        }
        $rowCount = $this->adapter->delete(
            $this->tableName,
            [
                $this->primaryKey => $id
            ]
        );
        return !empty($rowCount);
    }

    /**
     * Получить количество сущностей удовлетворяющих критерии
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria): int
    {
        $criteria['COLUMNS'] = sprintf('COUNT(%s)', $this->primaryKey);
        $count = $this->adapter->select($this->tableName, $criteria)
            ->fetchColumn(0);
        return filter_var($count, FILTER_VALIDATE_INT);
    }
}