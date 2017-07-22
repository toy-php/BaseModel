<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Aggregate as AggregateInterface;
use BaseModel\Interfaces\Command as CommandInterface;

abstract class Command implements CommandInterface
{

    /**
     * Агрегат над которым выполняется команда
     * @var AggregateInterface
     */
    protected $aggregate;

    public function __construct(AggregateInterface $aggregate)
    {
        $this->aggregate = $aggregate;
    }

}