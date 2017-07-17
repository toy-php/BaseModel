<?php

namespace BaseModel;

use BaseModel\Interfaces\Thenable as ThenableInterface;

class Thenable implements ThenableInterface
{

    /**
     * @var \SplStack
     */
    protected $stack;

    /**
     * @var callable
     */
    protected $behavior;

    /**
     * Middleware constructor.
     * @param callable $callable стартовая функция
     * @throws Exception
     */
    public function __construct($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new Exception('Неверная функция');
        }
        $this->stack = new \SplStack();
        $this->stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);
        $this->stack->push(function () use ($callable) {
            $arguments = func_get_args();
            return $callable(...$arguments);
        });
        $this->behavior = function ($callable, $next) {
            return function () use ($callable, $next) {
                $arguments = func_get_args();
                return $callable($next(...$arguments));
            };
        };
    }

    /**
     * Изменение поведения промежуточных функций
     * @param $callable
     * @return $this|static
     * @throws Exception
     */
    public function withBehavior($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new Exception('Неверная функция');
        }
        if ($this->behavior === $callable) {
            return $this;
        }
        $instance = clone $this;
        $instance->behavior = $callable;
        return $instance;
    }

    /**
     * Добавление функции в стек
     * @param callable $callable
     * @return ThenableInterface
     * @throws Exception
     */
    public function then($callable): ThenableInterface
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new Exception('Неверная функция');
        }
        $behavior = $this->behavior;
        $next = $this->stack->top();
        $this->stack->push($behavior($callable, $next));
        return $this;
    }

    /**
     * Выполнение стека функций
     */
    public function __invoke()
    {
        $arguments = func_get_args();
        $callable = $this->stack->top();
        return $callable(...$arguments);
    }
}