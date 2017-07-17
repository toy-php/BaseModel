<?php

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;

class Entity extends Subject implements EntityInterface
{

    /**
     * Идентификатор сущности
     * @var mixed
     */
    private $_id;

    /**
     * Атрибуты сущности
     * @var array
     */
    private $_attributes = [];

    /**
     * Отношения сущности
     * @var EntityInterface[]|CollectionInterface[]
     */
    private $_relations = [];

    /**
     * @var callable[]
     */
    private $_lazyFunctions = [];

    /**
     * Сохраненное состояние сущности
     * @var Memento
     */
    private $_lastState;

    /**
     * Ссылка на менеджер сущностей
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * Заполнить сущность данными,
     * при этом все предыдущие данные сущности удаляются
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        parent::__construct();
        $this->em = EntityManager::getInstance();
        foreach ($attributes as $name => $attribute) {
            $this->__set($name, $attribute);
        }
        $this->setFlag((!empty($this->_id)) ? self::FLAG_CLEAN : self::FLAG_NEW);
        $this->saveState();
    }

    /**
     * Сохранение состояния сущности
     */
    private function saveState()
    {
        $this->_lastState = new Memento([
            $this->_id,
            $this->_attributes,
            $this->getFlag()
        ]);
    }

    /**
     * Откат изменений
     */
    public function rollBack()
    {
        list($id, $attributes, $flag) = $this->_lastState->getState();
        $this->_id = $id;
        $this->_attributes = $attributes;
        $this->setFlag($flag);
    }

    /**
     * Получить экземпляр сущности
     * @param array $data
     * @return EntityInterface
     */
    public static function withData(array $data): EntityInterface
    {
        return new static($data);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @inheritdoc
     */
    public function withId($id): EntityInterface
    {
        if($this->_id === $id){
            return $this;
        }
        $instance = clone ($this);
        $instance->_id = $id;
        $instance->setFlag(self::FLAG_CLEAN);
        $instance->saveState();
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
        }elseif (isset($this->_lazyFunctions[$name])){
            $value = $this->_lazyFunctions[$name]($this);
            $this->_setAttribute($name, $value);
            return $value;
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
        }elseif (is_callable($value)){
            $this->_lazyFunctions[$name] = $value;
            return;
        }
        $this->_attributes[$name] = $value;
        $this->setFlag(self::FLAG_DIRTY);
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
     * Получить структуру в виде массива
     * @return array
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