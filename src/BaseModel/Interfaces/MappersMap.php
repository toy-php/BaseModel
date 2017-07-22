<?php

namespace BaseModel\Interfaces;


interface MappersMap
{

    /**
     * Загрузить преобразователь для раннее зарегисрированной сущности
     * @param string $entityClass
     * @return Mapper
     */
    public function loadMapper(string $entityClass): Mapper;
}