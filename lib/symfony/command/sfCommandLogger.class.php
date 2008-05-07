<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCommandLogger.class.php 6551 2007-12-17 18:48:03Z fabien $
 */
class sfCommandLogger extends sfConsoleLogger
{
  /**
   * Initializes this logger.
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance
   * @param  array             An array of options.
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $dispatcher->connect('command.log', array($this, 'listenToLogEvent'));

    return parent::initialize($dispatcher, $options);
  }

  /**
   * Listens to command.log events.
   *
   * @param sfEvent An sfEvent instance
   *
   */
  public function listenToLogEvent(sfEvent $event)
  {
    $priority = isset($event['priority']) ? $event['priority'] : self::INFO;
    unset($event['priority']);

    $prefix = '';
    if ('application.log' == $event->getName())
    {
      $subject  = $event->getSubject();
      $subject  = is_object($subject) ? get_class($subject) : (is_string($subject) ? $subject : 'main');

      $prefix = '>> '.$subject.' ';
    }

    foreach ($event->getParameters() as $message)
    {
      $this->log(sprintf('%s%s', $prefix, $message), $priority);
    }
  }
}
