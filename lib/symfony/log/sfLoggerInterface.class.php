<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoggerInterface is the interface all symfony loggers must implement.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfLoggerInterface.class.php 4847 2007-08-09 06:10:00Z fabien $
 */
interface sfLoggerInterface
{
  /**
   * Logs a message.
   *
   * @param string Message
   * @param string Message priority
   */
  public function log($message, $priority = null);
}
