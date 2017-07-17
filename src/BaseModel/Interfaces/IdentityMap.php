<?php

namespace BaseModel\Interfaces;

interface IdentityMap
{

    /**
     * Получить сущность, если имеется в карте
     * иначе сохранить в карту
     * @param Entity $entity
     * @return Entity
     */
    public function get(Entity $entity): Entity;


}