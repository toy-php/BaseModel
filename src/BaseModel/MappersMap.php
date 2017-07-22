<?php

namespace BaseModel;

use BaseModel\Interfaces\Mapper as MapperInterface;
use BaseModel\Interfaces\MappersMap as MappersMapInterface;

class MappersMap extends Container implements MappersMapInterface
{

    /**
     * Регистрация преобразователя сущности
     * @param string $entityClass
     * @param callable $lazyBuildMapper
     * @throws Exception
     */
    public function bindEntityWithMapper(string $entityClass, callable $lazyBuildMapper)
    {
        if($this->offsetExists($entityClass)){
            throw new Exception(
                sprintf(
                    'Для сущности "%s" уже зарегистрирован преобразователь',
                    $entityClass
                )
            );
        }
        $this->offsetSet($entityClass, $lazyBuildMapper);
    }

    /**
     * Загрузить преобразователь для раннее зарегисрированной сущности
     * @param string $entityClass
     * @return MapperInterface
     * @throws Exception
     */
    public function loadMapper(string $entityClass): MapperInterface
    {
        if(!$this->offsetExists($entityClass)){
            throw new Exception(
                sprintf(
                    'Для сущности "%s" не зарегистрирован преобразователь',
                    $entityClass
                )
            );
        }
        $mapper = $this->offsetGet($entityClass);
        if(!$mapper instanceof MapperInterface){
            throw new Exception(
                sprintf(
                    'Зарегистрированный преобразователь для сущности "%s" не реализует необходимый интерфейс',
                    $entityClass
                )
            );
        }
        return $mapper;
    }
}