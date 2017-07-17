<?php

namespace BaseModel;

use Psr\Log\LoggerInterface;
use SplSubject;
use BaseModel\Interfaces\Model as ModelInterface;

class Logger implements \SplObserver
{

    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Receive update from subject
     * @link http://php.net/manual/en/splobserver.update.php
     * @param SplSubject $subject <p>
     * The <b>SplSubject</b> notifying the observer of an update.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function update(SplSubject $subject)
    {
        if(!$subject instanceof ModelInterface){
            return;
        }
        if($subject->getFlag() === ModelInterface::FLAG_LOG){
            $this->logger->log(
                $subject->getLogLevel(),
                $subject->getLogMessage(),
                $subject->getLogContext()
            );
        }
    }
}