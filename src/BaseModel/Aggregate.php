<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Aggregate as AggregateInterface;

abstract class Aggregate extends Subject implements AggregateInterface
{

    protected $entityManager;

    public function __construct()
    {
        parent::__construct();
        $this->entityManager = EntityManager::getInstance();
        $this->setFlag(self::FLAG_CLEAN);
    }

    /**
     * Получить экземпляр агрегата
     * @param string $aggregateClass
     * @return AggregateInterface
     * @throws Exception
     */
    public static function create(string $aggregateClass): AggregateInterface
    {
        $aggregate = new $aggregateClass();
        if(!$aggregate instanceof AggregateInterface){
            throw new Exception(sprintf('Класс агрегата "%s" не реализует необходимый интерфейс', $aggregateClass));
        }
        return $aggregate;
    }

}