<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Entity as EntityInterface;
use BaseModel\Interfaces\IdentityMap as IdentityMapInterface;

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
        $type = get_class($entity);
        if (isset($this->_entities[$type][$id])) {
            return $this->_entities[$type][$id];
        }
        return $this->_entities[$type][$id] = $entity;
    }

}