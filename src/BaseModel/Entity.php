<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\Memento as MementoInterface;

class Entity extends Subject implements EntityInterface
{

    /**
     * Атрибуты сущности
     * @var array
     */
    private $_attributes = [];

    /**
     * Измененные атрибуты
     * @var array
     */
    private $_dirtyAttributes = [];

    /**
     * Отношения сущности
     * @var EntityInterface[]|CollectionInterface[]
     */
    private $_relations = [];

    /**
     * Функции
     * @var callable[]
     */
    private $functions = [];

    /**
     * Получить снимок состояния сущности
     * @return MementoInterface
     */
    public function createMemento(): MementoInterface
    {
        return new Memento([
            $this->_attributes,
            $this->_dirtyAttributes,
            $this->getFlag()
        ]);
    }

    /**
     * Вернуть состояние сущности из снимка состояния
     * @param MementoInterface $memento
     */
    public function setMemento(MementoInterface $memento)
    {
        list($attributes, $dirtyAttributes, $flag) = $memento->getState();
        $this->_attributes = $attributes;
        $this->_dirtyAttributes = $dirtyAttributes;
        $this->setFlag($flag);
    }

    /**
     * Получить экземпляр сущности с соответствующими данными
     * @param array $data
     * @return EntityInterface
     */
    public function withData(array $data): EntityInterface
    {
        $instance = clone $this;
        foreach ($data as $name => $attribute) {
            $instance->__set($name, $attribute);
        }
        $id = $instance->getId();
        $instance->setFlag((!empty($id)) ? self::FLAG_CLEAN : self::FLAG_NEW);
        return $instance;
    }

    /**
     * Получение атрибутов
     * @param $name
     * @return mixed|null
     */
    private function _getAttribute($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } elseif (isset($this->_relations[$name])) {
            return $this->_relations[$name];
        } elseif (isset($this->functions[$name])) {
            return $this->functions[$name]($this);
        }
        return null;
    }

    /**
     * Установка атрибутов
     * @param $name
     * @param $value
     */
    private function _setAttribute($name, $value)
    {
        if (($value instanceof EntityInterface
                or $value instanceof CollectionInterface)
            and !isset($this->_relations[$name])
        ) {
            $this->_relations[$name] = $value;
            return;
        } elseif (is_callable($value)) {
            $this->functions[$name] = $value;
            return;
        }
        $this->_attributes[$name] = $value;
        if ($this->getFlag() === self::FLAG_CLEAN) {
            $this->setFlag(self::FLAG_DIRTY);
        }
        if ($this->getFlag() === self::FLAG_DIRTY) {
            $this->_dirtyAttributes[$name] = $value;
        }
    }

    /**
     * Получение атрибутов сущности.
     * Если получаемы атрибут является относительной сущностью или коллекцией,
     * и если этой значение не сохранено в массиве относительных сущностей,
     * то происходит сохранение.
     *
     * @param $name
     * @return CollectionInterface|EntityInterface|mixed
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        if (($value instanceof EntityInterface
                or $value instanceof CollectionInterface)
            and !isset($this->_relations[$name])
        ) {
            return $this->_relations[$name] = $value;
        }
        return $value;
    }

    /**
     * Магия получения или установки атрибутов
     * через аксессоры и мутаторы соответственно
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (preg_match('/^get([a-z0-9_]+)/i', $method, $matches)) {
            $name = lcfirst($matches[1]);
            return $this->_getAttribute($name);
        }
        if (preg_match('/^set([a-z0-9_]+)/i', $method, $matches)) {
            $name = lcfirst($matches[1]);
            $value = array_shift($arguments);
            return $this->_setAttribute($name, $value);
        }
        return parent::__call($method, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * @inheritdoc
     */
    public function getDirtyAttributes(): array
    {
        return $this->_dirtyAttributes;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $output = [];
        foreach ($this->_attributes as $name => $attribute) {
            $output[$name] = $attribute;
        }
        /** @var EntityInterface|CollectionInterface $relation */
        foreach ($this->_relations as $name => $relation) {
            $output[$name] = $relation->toArray();
        }
        return $output;
    }
}