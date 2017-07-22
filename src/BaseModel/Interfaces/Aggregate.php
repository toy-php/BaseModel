<?php

namespace BaseModel\Interfaces;

interface Aggregate extends Subject
{

    /**
     * Флаг наличия ошибки при совершении транзакции
     */
    const FLAG_HAS_ERROR = 0xFE;

    /**
     * Флаг успешного завершения транзакции
     */
    const FLAG_COMPLETE = 0xFF;

    /**
     * Получить мета-данные
     * @return MetaData
     */
    public function getMetaData(): MetaData;

    /**
     * Устновка флага успешного завершения транзакции
     * @return void
     */
    public function setCompleted();

    /**
     * Устновка флага завершения транзакции с ошибкой
     * @return void
     */
    public function setHasError();
}