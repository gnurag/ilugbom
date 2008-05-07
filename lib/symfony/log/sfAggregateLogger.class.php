<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAggregateLogger logs messages through several loggers.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfAggregateLogger.class.php 5109 2007-09-15 09:41:13Z fabien $
 */
class sfAggregateLogger extends sfLogger
{
  protected
    $dispatcher = null,
    $loggers = array();

  /**
   * Initializes this logger.
   *
   * Available options:
   *
   * - loggers: Logger objects that extends sfLogger.
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance
   * @param  array        An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;

    if (isset($options['loggers']))
    {
      if (!is_array($options['loggers']))
      {
        $options['loggers'] = array($options['loggers']);
      }

      $this->addLoggers($options['loggers']);
    }

    return parent::initialize($dispatcher, $options);
  }

  /**
   * Retrieves current loggers.
   *
   * @return array List of loggers
   */
  public function getLoggers()
  {
    return $this->loggers;
  }

  /**
   * Adds an array of loggers.
   *
   * @param object An array of Logger objects
   */
  public function addLoggers($loggers)
  {
    foreach ($loggers as $logger)
    {
      $this->addLogger($logger);
    }
  }

  /**
   * Adds a logger.
   *
   * @param object The Logger object
   */
  public function addLogger(sfLogger $logger)
  {
    $this->loggers[] = $logger;

    $this->dispatcher->disconnect('application.log', array($logger, 'listenToLogEvent'));
  }

  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  protected function doLog($message, $priority)
  {
    foreach ($this->loggers as $logger)
    {
      $logger->log($message, $priority);
    }
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown()
  {
    foreach ($this->loggers as $logger)
    {
      $logger->shutdown();
    }

    $this->loggers = array();
  }
}
