<?php

namespace BaseModel;

use BaseModel\Interfaces\Model as ModelInterface;
use BaseModel\Interfaces\EntityManager as EntityManagerInterface;

abstract class Model extends Subject implements ModelInterface
{

    private $logLevel;
    private $logMessage;
    private $logContext = [];
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
        return $this->logLevel;
    }

    /**
     * @inheritdoc
     */
    public function getLogMessage():string
    {
        return $this->logMessage;
    }

    /**
     * @inheritdoc
     */
    public function getLogContext(): array
    {
        return $this->logContext;
    }

    /**
     * Запись в журнал
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->logLevel = $level;
        $this->logMessage = $message;
        $this->logContext = $context;
        $this->setFlag(self::FLAG_LOG);
        $this->logLevel = null;
        $this->logMessage = null;
        $this->logContext = [];
    }

}