<?php

namespace BaseModel\Interfaces;

interface Thenable
{

    /**
     * Инициализация стека функций для стартовой функции
     * @param $callable
     */
    public function __construct($callable);

    /**
     * Добавление функции в стек
     * @param callable $callable
     * @return Thenable
     */
    public function then($callable): Thenable;

    /**
     * Выполнение стека функций
     * @return void
     */
    public function __invoke();
}