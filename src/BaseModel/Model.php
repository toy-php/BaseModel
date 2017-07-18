<?php

declare(strict_types=1);

namespace BaseModel;

use BaseModel\Interfaces\Model as ModelInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;

abstract class Model extends Subject implements ModelInterface
{

    private $_logLevel;
    private $_logMessage;
    private $_logContext = [];
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->setFlag(self::FLAG_CLEAN);
    }

    /**
     * @inheritdoc
     */
    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    /**
     * @inheritdoc
     */
    public function getLogMessage(): string
    {
        return $this->_logMessage;
    }

    /**
     * @inheritdoc
     */
    public function getLogContext(): array
    {
        return $this->_logContext;
    }

    /**
     * Запись в журнал
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->_logLevel = $level;
        $this->_logMessage = $message;
        $this->_logContext = $context;
        $this->setFlag(self::FLAG_LOG);
        $this->_logLevel = null;
        $this->_logMessage = null;
        $this->_logContext = [];
    }

}