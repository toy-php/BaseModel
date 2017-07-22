<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Aggregate as AggregateInterface;
use BaseModel\Interfaces\MetaData as MetaDataInterface;

class Aggregate extends Subject implements AggregateInterface
{

    /**
     * Объект метаданных
     * @var MetaDataInterface|null
     */
    private $metaData;

    /**
     * Сохранение состояния агрегата
     */
    public function persist()
    {
        $this->entityManager->persist();
    }

    /**
     * Установить мета-данные
     * @param MetaDataInterface $metaData
     */
    public function setMetaData(MetaDataInterface $metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Получить мета-данные
     * @return MetaDataInterface
     */
    public function getMetaData(): MetaDataInterface
    {
        return $this->metaData ?: new MetaData([]);
    }

    /**
     * Устновка флага успешного завершения транзакции
     * @return void
     */
    public function setCompleted()
    {
        $this->setFlag(self::FLAG_COMPLETE);
    }

    /**
     * Устновка флага завершения транзакции с ошибкой
     * @return void
     */
    public function setHasError()
    {
        $this->setFlag(self::FLAG_HAS_ERROR);
    }
}