<?php

declare(strict_types=1);

namespace BaseModel;


use BaseModel\Interfaces\EntityManager as EntityManagerInterface;
use BaseModel\Interfaces\Subject as SubjectInterface;
use SplObserver;

class Subject extends Object implements SubjectInterface
{

    /**
     * Идентификатор субъекта
     * @var string
     */
    private $_id;

    /**
     * Массив наблюдателей за субъектом
     * @var \SplObjectStorage
     */
    private $_observers;

    /**
     * Флаг состояния объекта
     * @var int
     */
    private $_flag;

    /**
     * Ссылка на менеджер сущностей
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct()
    {
        $this->entityManager = AbstractEntityManager::getEntityManager();
        $this->_observers = new \SplObjectStorage();
        $this->setFlag(self::FLAG_EMPTY);
    }

    public function __clone()
    {
        $this->_observers = new \SplObjectStorage();
    }

    /**
     * Получить экземпляр субъекта
     * @param string $subjectClass
     * @return SubjectInterface
     * @throws Exception
     */
    public static function create(string $subjectClass): SubjectInterface
    {
        $subject = new $subjectClass();
        if(!$subject instanceof SubjectInterface){
            throw new Exception(
                sprintf('Класс субъекта "%s" не реализует необходимый интерфейс', $subjectClass)
            );
        }
        return $subject;
    }

    /**
     * Установить флаг состояния субъекта
     * @param int $flag
     */
    protected function setFlag(int $flag)
    {
        $this->_flag = $flag;
        $this->notify();
    }

    /**
     * Получить флаг состояния субъекта
     * @return int
     */
    public function getFlag(): int
    {
        return $this->_flag;
    }

    /**
     * @inheritdoc
     */
    public function setId(string $id)
    {
        $this->_id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Установить свойство
     * При установке свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
        $this->setFlag(self::FLAG_DIRTY);
    }

    /**
     * Удалить свойство
     * При удалении свойства должно происходить оповещение наблюдателей об изменении состояния
     * @param $name
     */
    public function __unset($name)
    {
        parent::__unset($name);
        $this->setFlag(self::FLAG_DIRTY);
    }

    /**
     * Добавить наблюдателя за субъектом
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        if(!$this->_observers->contains($observer)){
            $this->_observers->attach($observer);
        }
    }

    /**
     * Исключить наблюдателя за субъектом
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        if($this->_observers->contains($observer)){
            $this->_observers->detach($observer);
        }
    }

    /**
     * Оповестить наблюдателей об изменении состояния
     */
    public function notify()
    {
        /** @var SplObserver $observer */
        foreach ($this->_observers as $observer) {
            $observer->update($this);
        }
    }
}