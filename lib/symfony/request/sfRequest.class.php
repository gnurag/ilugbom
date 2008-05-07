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
 * sfRequest provides methods for manipulating client request information such
 * as attributes, and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id: sfRequest.class.php 8802 2008-05-05 17:57:42Z FabianLange $
 */
abstract class sfRequest
{
  /**
   * Process validation and execution for only GET requests.
   *
   */
  const GET = 2;

  /**
   * Skip validation and execution for any request method.
   *
   */
  const NONE = 1;

  /**
   * Process validation and execution for only POST requests.
   *
   */
  const POST = 4;

  /**
   * Process validation and execution for only PUT requests.
   *
   */
  const PUT = 5;

  /**
   * Process validation and execution for only DELETE requests.
   *
   */
  const DELETE = 6;

  /**
   * Process validation and execution for only HEAD requests.
   *
   */
  const HEAD = 7;

  protected
    $errors          = array(),
    $dispatcher      = null,
    $method          = null,
    $parameterHolder = null,
    $config          = null,
    $attributeHolder = null;

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    $this->initialize($dispatcher, $parameters, $attributes);
  }

  /**
   * Initializes this sfRequest.
   *
   * @param  sfEventDispatcher  A sfEventDispatcher instance
   * @param  array              An associative array of initialization parameters
   * @param  array              An associative array of initialization attributes
   *
   * @return Boolean            true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    $this->dispatcher = $dispatcher;

    // initialize parameter and attribute holders
    $this->parameterHolder = new sfParameterHolder();
    $this->attributeHolder = new sfParameterHolder();

    $this->parameterHolder->add($parameters);
    $this->attributeHolder->add($attributes);
  }

  /**
   * Extracts parameter values from the request.
   *
   * @param array An indexed array of parameter names to extract
   *
   * @return array An associative array of parameters and their values. If
   *               a specified parameter doesn't exist an empty string will
   *               be returned for its value
   */
  public function extractParameters($names)
  {
    $array = array();

    $parameters = $this->parameterHolder->getAll();
    foreach ($parameters as $key => $value)
    {
      if (in_array($key, $names))
      {
        $array[$key] = $value;
      }
    }

    return $array;
  }

  /**
   * Retrieves an error message.
   *
   * @param string An error name
   *
   * @return string An error message, if the error exists, otherwise null
   */
  public function getError($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return isset($this->errors[$name]) ? $this->errors[$name] : null;
  }

  /**
   * Retrieves an array of error names.
   *
   * @return array An indexed array of error names
   */
  public function getErrorNames()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return array_keys($this->errors);
  }

  /**
   * Retrieves an array of errors.
   *
   * @return array An associative array of errors
   */
  public function getErrors()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->errors;
  }

  /**
   * Retrieves this request's method.
   *
   * @return int One of the following constants:
   *             - sfRequest::GET
   *             - sfRequest::POST
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Indicates whether or not an error exists.
   *
   * @param string An error name
   *
   * @return boolean true, if the error exists, otherwise false
   */
  public function hasError($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return array_key_exists($name, $this->errors);
  }

  /**
   * Indicates whether or not any errors exist.
   *
   * @return boolean true, if any error exist, otherwise false
   */
  public function hasErrors()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return count($this->errors) > 0;
  }

  /**
   * Removes an error.
   *
   * @param string An error name
   *
   * @return string An error message, if the error was removed, otherwise null
   */
  public function removeError($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    $retval = null;

    if (isset($this->errors[$name]))
    {
      $retval = $this->errors[$name];

      unset($this->errors[$name]);
    }

    return $retval;
  }

  /**
   * Sets an error.
   *
   * @param string An error name
   * @param string An error message
   *
   */
  public function setError($name, $message)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Error in form for parameter "%s" (with message "%s")', $name, $message))));
    }

    $this->errors[$name] = $message;
  }

  /**
   * Sets an array of errors
   *
   * If an existing error name matches any of the keys in the supplied
   * array, the associated message will be overridden.
   *
   * @param array An associative array of errors and their associated messages
   *
   */
  public function setErrors($errors)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    $this->errors = array_merge($this->errors, $errors);
  }

  /**
   * Sets the request method.
   *
   * @param int One of the following constants:
   *
   * - sfRequest::GET
   * - sfRequest::POST
   * - sfRequest::PUT
   * - sfRequest::DELETE
   * - sfRequest::HEAD
   *
   * @return void
   *
   * @throws <b>sfException</b> - If the specified request method is invalid
   */
  public function setMethod($methodCode)
  {
    $available_methods = array(self::GET, self::POST, self::PUT, self::DELETE, self::HEAD, self::NONE);
    if (in_array($methodCode, $available_methods))
    {
      $this->method = $methodCode;

      return;
    }

    // invalid method type
    throw new sfException(sprintf('Invalid request method: %s.', $methodCode));
  }

  /**
   * Retrieves the parameters for the current request.
   *
   * @return sfParameterHolder The parameter holder
   */
  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves the attributes holder.
   *
   * @return sfParameterHolder The attribute holder
   */
  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  /**
   * Retrieves an attribute from the current request.
   *
   * @param string Attribute name
   * @param string Default attribute value
   *
   * @return mixed An attribute value
   */
  public function getAttribute($name, $default = null)
  {
    return $this->attributeHolder->get($name, $default);
  }

  /**
   * Indicates whether or not an attribute exist for the current request.
   *
   * @param string Attribute name
   *
   * @return boolean true, if the attribute exists otherwise false
   */
  public function hasAttribute($name)
  {
    return $this->attributeHolder->has($name);
  }

  /**
   * Sets an attribute for the request.
   *
   * @param string Attribute name
   * @param string Value for the attribute
   *
   */
  public function setAttribute($name, $value)
  {
    $this->attributeHolder->set($name, $value);
  }

  /**
   * Retrieves a paramater for the current request.
   *
   * @param string Parameter name
   * @param string Parameter default value
   *
   */
  public function getParameter($name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Indicates whether or not a parameter exist for the current request.
   *
   * @param string Parameter name
   *
   * @return boolean true, if the paramater exists otherwise false
   */
  public function hasParameter($name)
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets a parameter for the current request.
   *
   * @param string Parameter name
   * @param string Parameter value
   *
   */
  public function setParameter($name, $value)
  {
    $this->parameterHolder->set($name, $value);
  }

  /**
   * Calls methods defined via sfEventDispatcher.
   *
   * @param string The method name
   * @param array  The method arguments
   *
   * @return mixed The returned value of the called method
   *
   * @throws <b>sfException</b> if call fails
   */
  public function __call($method, $arguments)
  {
    $event = $this->dispatcher->notifyUntil(new sfEvent($this, 'request.method_not_found', array('method' => $method, 'arguments' => $arguments)));
    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
    }

    return $event->getReturnValue();
  }
}
