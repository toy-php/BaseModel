<?php

namespace BaseModel;

use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\MetaData as MetaDataInterface;
use Traversable;

class Collection implements CollectionInterface
{

    /**
     * Тип сущностей коллекции
     * @var string
     */
    private $_type;

    /**
     * Массив сущностей
     * @var \ArrayObject
     */
    private $_entities;

    /**
     * Мета-данные коллекции
     * @var MetaDataInterface
     */
    private $_metaData;

    public function __construct(string $entityClass)
    {
        $this->_type = $entityClass;
        $this->_entities = new \ArrayObject();
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * Проверка типа сущности
     * @param EntityInterface $entity
     * @throws Exception
     */
    protected function checkType(EntityInterface $entity)
    {
        if (!$entity instanceof $this->_type) {
            throw new Exception('Неверный тип объекта');
        }
    }

    /**
     * @inheritdoc
     */
    public static function withEntities(string $entityClass, array $entities): CollectionInterface
    {
        $instance = new static($entityClass);
        foreach ($entities as $entity) {
            $instance = $instance->withEntity($entity);
        }
        return $instance;
    }

    /**
     * Получить коллекцию с мета-данными
     * @param MetaDataInterface $metaData
     * @return CollectionInterface
     */
    public function withMeta(MetaDataInterface $metaData): CollectionInterface
    {
        if($this->_metaData === $metaData){
            return $this;
        }
        $instance = clone $this;
        $instance->_metaData = $metaData;
        return $instance;
    }

    /**
     * Получить мета-данные коллекции
     * @return MetaDataInterface
     */
    public function getMetaData(): MetaDataInterface
    {
        return $this->_metaData;
    }

    /**
     * @inheritdoc
     */
    public function withEntity(EntityInterface $entity): CollectionInterface
    {
        $this->checkType($entity);
        $key = array_search($entity, $this->_entities->getArrayCopy());
        if (!empty($key)) {
            return $this;
        }
        $instance = clone $this;
        $instance->_entities->append($entity);
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function withoutEntity(EntityInterface $entity): CollectionInterface
    {
        $this->checkType($entity);
        $key = array_search($entity, $this->_entities->getArrayCopy());
        if (empty($key)) {
            return $this;
        }
        $instance = clone $this;
        $instance->_entities->offsetUnset($key);
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset): EntityInterface
    {
        return $this->_entities->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('Коллекцию нельзя менять');
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return $this->_entities->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Коллекцию нельзя менять');
    }

    /**
     * Очистить коллекцию
     */
    public function clear()
    {
        $this->_entities->exchangeArray([]);
    }

    /**
     * Фильтрация коллекции.
     * Обходит каждый объект коллекции,
     * передавая его в callback-функцию.
     * Если callback-функция возвращает true,
     * данный объект из текущей коллекции попадает в результирующую коллекцию.
     * @param callable $function
     * @return CollectionInterface
     */
    public function filter(callable $function): CollectionInterface
    {
        $instance = clone $this;
        $instance->_entities->exchangeArray(
            array_filter($this->_entities->getArrayCopy(), $function)
        );
        return $instance;
    }

    /**
     * Перебор всех объектов коллекции.
     * Возвращает новую коллекцию,
     * содержащую объекты после их обработки callback-функцией.
     * @param callable $function
     * @return CollectionInterface
     */
    public function map(callable $function): CollectionInterface
    {
        $instance = clone $this;
        $instance->_entities->exchangeArray(
            array_map($function, $this->_entities->getArrayCopy())
        );
        return $instance;
    }

    /**
     * Итеративно уменьшает коллекцию к единственному значению
     * @param callable $function
     * @param mixed $initial
     * @return mixed
     */
    public function reduce(callable $function, $initial = null)
    {
        return array_reduce($this->_entities->getArrayCopy(), $function, $initial);
    }

    /**
     * Сортировка коллекции
     * @param callable $function
     */
    public function sort(callable $function)
    {
        $this->_entities->uasort($function);
    }

    /**
     * Сортировать по полю
     * @param string $fieldName
     * @param string $direction
     * @return CollectionInterface
     * @throws Exception
     */
    public function sortByField(string $fieldName, string $direction = 'asc'): CollectionInterface
    {
        $direction = strtolower($direction);
        if($direction != 'asc' or $direction != 'desc'){
            throw new Exception('Неизвестное направление сортировки');
        }
        $this->sort(function ($a, $b) use ($fieldName, $direction) {
            if (is_numeric($a->{$fieldName}) or is_numeric($b->{$fieldName})) {
                $compare = ($a->{$fieldName} <=> $b->{$fieldName});
            }else{
                $compare = strcmp(strtolower($a->{$fieldName}),strtolower($b->{$fieldName}));
            }
            return $direction == 'desc' ? -$compare : $compare;
        });
        return $this;
    }

    /**
     * Сортировка коллекции по ключам
     * @param string $direction
     * @return CollectionInterface
     * @throws Exception
     */
    public function keySort(string $direction = 'asc'): CollectionInterface
    {
        $direction = strtolower($direction);
        if($direction != 'asc' and $direction != 'desc'){
            throw new Exception('Неизвестное направление сортировки');
        }
        $this->_entities->uksort(function ($a, $b) use ($direction){
            $compare = ($a <=> $b);
            return $direction == 'desc' ? -$compare : $compare;
        });
        return $this;
    }

    /**
     * Поиск объекта по значению свойства
     * @param $property
     * @param $value
     * @return EntityInterface|null
     */
    public function search($property, $value)
    {
        $offset = array_search($value, array_column($this->_entities->getArrayCopy(), $property));
        if ($offset !== false and $offset >= 0) {
            return $this->offsetGet($offset);
        }
        return null;
    }

    /**
     * Преобразовать коллекцию в массив
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        /** @var EntityInterface $entity */
        foreach ($this->_entities as $key => $entity) {
            $result[$key] = $entity->toArray();
        }
        return $result;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->_entities->getIterator();
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->_entities->count();
    }

}