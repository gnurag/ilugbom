<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPathInfoRouting class is a very simple routing class that uses PATH_INFO.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPathInfoRouting.class.php 7779 2008-03-08 18:08:36Z fabien $
 */
class sfPathInfoRouting extends sfRouting
{
  protected
    $currentRouteParameters = array();

  /**
   * @see sfRouting
   */
  public function getCurrentInternalUri($with_route_name = false)
  {
    $parameters = $this->currentRouteParameters;

    // other parameters
    unset($parameters['module'], $parameters['action']);
    ksort($parameters);
    $parameters = count($parameters) ? '?'.http_build_query($parameters, null, '&') : '';

    return sprintf('%s/%s%s', $this->currentRouteParameters['module'], $this->currentRouteParameters['action'], $parameters);
  }

 /**
  * @see sfRouting
  */
  public function generate($name, $params = array(), $querydiv = '/', $divider = '/', $equals = '/')
  {
    $url = '';
    foreach ($this->mergeArrays($this->defaultParameters, $params) as $key => $value)
    {
      $url .= '/'.$key.'/'.$value;
    }

    return $url ? $url : '/';
  }

 /**
  * @see sfRouting
  */
  public function parse($url)
  {
    $this->currentRouteParameters = $this->defaultParameters;
    $array = explode('/', trim($url, '/'));
    $count = count($array);

    for ($i = 0; $i < $count; $i++)
    {
      // see if there's a value associated with this parameter, if not we're done with path data
      if ($count > ($i + 1))
      {
        $this->currentRouteParameters[$array[$i]] = $array[++$i];
      }
    }

    $this->currentRouteParameters = $this->fixDefaults($this->currentRouteParameters);

    return $this->currentRouteParameters;
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
