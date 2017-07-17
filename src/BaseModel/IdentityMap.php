<?php

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use ORM\Interfaces\IdentityMap as IdentityMapInterface;

class IdentityMap implements IdentityMapInterface
{
    /**
     * @var EntityInterface[]
     */
    private $_entities = [];

    /**
     * Получить сущность, если имеется в карте
     * иначе сохранить в карту
     * @param  EntityInterface $entity
     * @return EntityInterface
     * @throws Exception
     */
    public function get(EntityInterface $entity): EntityInterface
    {
        $id = $entity->getId();
        if (empty($id)) {
            throw new Exception('Не определен идентификатор сущности');
        }
        if (isset($this->_entities[$id])) {
            return $this->_entities[$id];
        }
        return $this->_entities[$id] = $entity;
    }

}