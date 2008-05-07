<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfNoRouting class is a very simple routing class that uses GET parameters.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfNoRouting.class.php 7779 2008-03-08 18:08:36Z fabien $
 */
class sfNoRouting extends sfRouting
{
  /**
   * @see sfRouting
   */
  public function getCurrentInternalUri($with_route_name = false)
  {
    $parameters = $this->fixDefaults($this->mergeArrays($this->defaultParameters, $_GET));
    $action = sprintf('%s/%s', $parameters['module'], $parameters['action']);

    // other parameters
    unset($parameters['module'], $parameters['action']);
    ksort($parameters);
    $parameters = count($parameters) ? '?'.http_build_query($parameters, null, '&') : '';

    return sprintf('%s%s', $action, $parameters);
  }

 /**
  * @see sfRouting
  */
  public function generate($name, $params = array(), $querydiv = '/', $divider = '/', $equals = '/')
  {
    $parameters = http_build_query($this->mergeArrays($this->defaultParameters, $params), null, '&');

    return '/'.($parameters ? '?'.$parameters : '');
  }

 /**
  * @see sfRouting
  */
  public function parse($url)
  {
    return array();
  }

  /**
   * @see sfRouting
   */
  public function getRoutes()
  {
    return array();
  }

  /**
   * @see sfRouting
   */
  public function setRoutes($routes)
  {
    return array();
  }

  /**
   * @see sfRouting
   */
  public function hasRoutes()
  {
    return false;
  }

  /**
   * @see sfRouting
   */
  public function clearRoutes()
  {
  }
}
