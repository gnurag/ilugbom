<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id: sfConsoleRequest.class.php 7792 2008-03-09 22:06:59Z fabien $
 */
class sfConsoleRequest extends sfRequest
{
  /**
   * Initializes this sfRequest.
   *
   * @param sfEventDispatcher  A sfEventDispatcher instance
   * @param array         An associative array of initialization parameters
   * @param array         An associative array of initialization attributes
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    parent::initialize($dispatcher, $parameters, $attributes);

    $this->getParameterHolder()->add($_SERVER['argv']);
  }
}
