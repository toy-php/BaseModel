<?php

namespace BaseModel;

use BaseModel\Interfaces\MetaData as MetaDataInterface;

class MetaData extends ValueObject implements MetaDataInterface
{

    /**
     * @var array
     */
    protected $_attributes = [];

    public function __construct(array $data)
    {
        $this->_attributes = $data;
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
        }
        return null;
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
        return parent::__call($method, $arguments);
    }
}