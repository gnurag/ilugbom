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
 * sfBasicSecurityUser will handle any type of data as a credential.
 *
 * @package    symfony
 * @subpackage user
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id: sfBasicSecurityUser.class.php 7970 2008-03-19 12:37:29Z fabien $
 */
class sfBasicSecurityUser extends sfUser implements sfSecurityUser
{
  const LAST_REQUEST_NAMESPACE = 'symfony/user/sfUser/lastRequest';
  const AUTH_NAMESPACE = 'symfony/user/sfUser/authenticated';
  const CREDENTIAL_NAMESPACE = 'symfony/user/sfUser/credentials';

  protected $lastRequest = null;

  protected $credentials = null;
  protected $authenticated = null;

  protected $timedout = false;

  /**
   * Clears all credentials.
   *
   */
  public function clearCredentials()
  {
    $this->credentials = null;
    $this->credentials = array();
  }

  /**
   * returns an array containing the credentials
   */
  public function listCredentials()
  {
    return $this->credentials;
  }

  /**
   * Removes a credential.
   *
   * @param  mixed credential
   */  
  public function removeCredential($credential)
  {
    if ($this->hasCredential($credential))
    {
      foreach ($this->credentials as $key => $value)
      {
        if ($credential == $value)
        {
          if ($this->options['logging'])
          {
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Remove credential "%s"', $credential))));
          }

          unset($this->credentials[$key]);
          return;
        }
      }
    }
  }  

  /**
   * Adds a credential.
   *
   * @param  mixed credential
   */
  public function addCredential($credential)
  {
    $this->addCredentials(func_get_args());
  }

  /**
   * Adds several credential at once.
   *
   * @param  mixed array or list of credentials
   */
  public function addCredentials()
  {
    if (func_num_args() == 0) return;

    // Add all credentials
    $credentials = (is_array(func_get_arg(0))) ? func_get_arg(0) : func_get_args();

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Add credential(s) "%s"', implode(', ', $credentials)))));
    }

    foreach ($credentials as $aCredential)
    {
      if (!in_array($aCredential, $this->credentials))
      {
        $this->credentials[] = $aCredential;
      }
    }
  }

  
  /**
   * Returns true if user has credential.
   *
   * @param  mixed credentials
   * @param  boolean useAnd specify the mode, either AND or OR
   * @return boolean
   *
   * @author Olivier Verdier <Olivier.Verdier@free.fr>
   */
  public function hasCredential($credentials, $useAnd = true)
  {
    if (!is_array($credentials))
    {
      return in_array($credentials, $this->credentials);
    }

    // now we assume that $credentials is an array
    $test = false;

    foreach ($credentials as $credential)
    {
      // recursively check the credential with a switched AND/OR mode
      $test = $this->hasCredential($credential, $useAnd ? false : true);

      if ($useAnd)
      {
        $test = $test ? false : true;
      }

      if ($test) // either passed one in OR mode or failed one in AND mode
      {
        break; // the matter is settled
      }
    }

    if ($useAnd) // in AND mode we succeed if $test is false
    {
      $test = $test ? false : true;
    }

    return $test;
  }

  /**
   * Returns true if user is authenticated.
   *
   * @return boolean
   */
  public function isAuthenticated()
  {
    return $this->authenticated;
  }

  /**
   * Sets authentication for user.
   *
   * @param  boolean
   */
  public function setAuthenticated($authenticated)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('User is %sauthenticated', $authenticated === true ? '' : 'not '))));
    }

    if ($authenticated === true)
    {
      $this->authenticated = true;
    }
    else
    {
      $this->authenticated = false;
      $this->clearCredentials();
    }
  }

  public function setTimedOut()
  {
    $this->timedout = true;
  }

  public function isTimedOut()
  {
    return $this->timedout;
  }

  /**
   * Returns the timestamp of the last user request.
   *
   * @param  integer
   */
  public function getLastRequestTime()
  {
    return $this->lastRequest;
  }

  /**
   * Available options:
   *
   *  * timeout: Timeout to automatically log out the user in seconds (1800 by default)
   *             Set to false to disable
   *
   * @see sfUser
   */
  public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
  {
    // initialize parent
    parent::initialize($dispatcher, $storage, $options);

    if (!array_key_exists('timeout', $this->options))
    {
      $this->options['timeout'] = 1800;
    }

    // read data from storage
    $this->authenticated = $storage->read(self::AUTH_NAMESPACE);
    $this->credentials   = $storage->read(self::CREDENTIAL_NAMESPACE);
    $this->lastRequest   = $storage->read(self::LAST_REQUEST_NAMESPACE);

    if (is_null($this->authenticated))
    {
      $this->authenticated = false;
      $this->credentials   = array();
    }
    else
    {
      // Automatic logout logged in user if no request within timeout parameter seconds
      $timeout = $this->options['timeout'];
      if (false !== $timeout && !is_null($this->lastRequest) && time() - $this->lastRequest >= $timeout)
      {
        if ($this->options['logging'])
        {
          $this->dispatcher->notify(new sfEvent($this, 'application.log', array('Automatic user logout due to timeout')));
        }

        $this->setTimedOut();
        $this->setAuthenticated(false);
      }
    }

    $this->lastRequest = time();
  }

  public function shutdown()
  {
    // write the last request time to the storage
    $this->storage->write(self::LAST_REQUEST_NAMESPACE, $this->lastRequest);

    $this->storage->write(self::AUTH_NAMESPACE,         $this->authenticated);
    $this->storage->write(self::CREDENTIAL_NAMESPACE,   $this->credentials);

    // call the parent shutdown method
    parent::shutdown();
  }
}
