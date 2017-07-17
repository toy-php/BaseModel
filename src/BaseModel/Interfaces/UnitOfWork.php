<?php

namespace BaseModel\Interfaces;

interface UnitOfWork
{

    /**
     * Поставить сущность в очередь на сохранение
     * @param Entity $entity
     * @return Thenable
     */
    public function save(Entity $entity): Thenable;

    /**
     * Поставить сущность в очередь на удаление
     * @param Entity $entity
     * @return Thenable
     */
    public function remove(Entity $entity): Thenable;

    /**
     * Сохранение изменения
     */
    public function commit();

    /**
     * Очистить карту состояний
     */
    public function rollBack();

}