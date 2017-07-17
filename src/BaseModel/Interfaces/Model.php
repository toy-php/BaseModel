<?php

namespace BaseModel\Interfaces;

interface Model extends Subject
{

    /**
     * Флаг исходного состояние модели
     */
    const FLAG_CLEAN = 0x00;

    /**
     * Флаг необходимости журналирования состояния
     */
    const FLAG_LOG = 0x01;

    /**
     * Получить уровень сообщения журнала
     * @return mixed
     */
    public function getLogLevel();

    /**
     * Получить сообщение журнала
     * @return string
     */
    public function getLogMessage(): string;

    /**
     * Получить контест
     * @return array
     */
    public function getLogContext(): array;
}