<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\Collection as CollectionInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;

class Entity extends Subject implements EntityInterface
{

    /**
     * Идентификатор сущности
     * @var string
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
     * Функции
     * @var callable[]
     */
    private $functions = [];

    /**
     * Сохраненное состояние сущности
     * @var Memento
     */
    private $_lastState;

    /**
     * Ссылка на менеджер сущностей
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Entity constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->entityManager = EntityManager::getInstance();
        $this->setFlag(self::FLAG_NEW);
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
     * @param string $entityClass
     * @return EntityInterface
     * @throws Exception
     */
    public static function create(string $entityClass): EntityInterface
    {
        $entity = new $entityClass();
        if(!$entity instanceof EntityInterface){
            throw new Exception(sprintf('Класс сущности "%s" не реализует необходимый интерфейс', $entityClass));
        }
        return $entity;
    }

    /**
     * Получить экземпляр сущности с соответствующими данными
     * @param array $data
     * @return EntityInterface
     */
    public function withData(array $data): EntityInterface
    {
        $instance = clone ($this);
        foreach ($data as $name => $attribute) {
            $instance->__set($name, $attribute);
        }
        $instance->setFlag((!empty($instance->_id)) ? self::FLAG_CLEAN : self::FLAG_NEW);
        $instance->saveState();
        return $instance;
    }

    /**
     * Получить экземпляр сущности с соответствующим идентификатором
     * @param string $id
     * @return EntityInterface
     */
    public function withId(string $id): EntityInterface
    {
        if ($this->_id === $id) {
            return $this;
        }
        $instance = clone ($this);
        $instance->_id = $id;
        $instance->setFlag(self::FLAG_CLEAN);
        $instance->saveState();
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_id;
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