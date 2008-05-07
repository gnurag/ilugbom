<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfFormPropel.class.php 7845 2008-03-12 22:36:14Z fabien $
 */

/**
 * sfFormPropel is the base class for forms based on Propel objects.
 *
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfFormPropel.class.php 7845 2008-03-12 22:36:14Z fabien $
 */
abstract class sfFormPropel extends sfForm
{
  protected
    $isNew    = true,
    $cultures = array(),
    $object   = null;

  /**
   * Constructor.
   *
   * @param BaseObject A Propel object used to initialize default values
   * @param array      An array of options
   * @param string     A CSRF secret (false to disable CSRF protection, null to use the global CSRF secret)
   *
   * @see sfForm
   */
  public function __construct(BaseObject $object = null, $options = array(), $CSRFSecret = null)
  {
    $class = $this->getModelName();
    if (is_null($object))
    {
      $this->object = new $class();
    }
    else
    {
      if (!$object instanceof $class)
      {
        throw new sfException(sprintf('The "%s" form only accepts a "%s" object.', get_class($this), $class));
      }

      $this->object = $object;
      $this->isNew = false;
    }

    parent::__construct(array(), $options, $CSRFSecret);

    $this->updateDefaultsFromObject();
  }

  /**
   * Returns the default connection for the current model.
   *
   * @return Connection A database connection
   */
  public function getConnection()
  {
    return Propel::getConnection(constant(sprintf('%sPeer::DATABASE_NAME', $this->getModelName())));
    return Propel::getConnection(constant(sprintf('%s::DATABASE_NAME', get_class($this->object->getPeer()))));
  }

  /**
   * Returns the current model name.
   */
  abstract public function getModelName();

  /**
   * Returns true if the current form embeds a new object.
   *
   * @return Boolean true if the current form embeds a new object, false otherwise
   */
  public function isNew()
  {
    return $this->isNew;
  }

  /**
   * Embeds i18n objects into the current form.
   *
   * @param array An array of cultures
   * @param string The format to use for widget name
   * @param string A HTML decorator for the embedded form
   */
  public function embedI18n($cultures, $nameFormat = null, $decorator = null)
  {
    if (!$this->isI18n())
    {
      throw new sfException(sprintf('The model "%s" is not internationalized.', $this->getModelName()));
    }

    $this->cultures = $cultures;

    $class = $this->getI18nFormClass();
    $i18n = new $class();
    foreach ($cultures as $culture)
    {
      $this->embedForm($culture, $i18n, $nameFormat, $decorator);
    }
  }

  /**
   * Returns the current object for this form.
   *
   * @return BaseObject The current object.
   */
  public function getObject()
  {
    return $this->object;
  }

  /**
   * Binds the current form and save the to the database in one step.
   *
   * @param  array      An array of tainted values to use to bind the form
   * @param  Connection An optional Propel Connection object
   *
   * @return Boolean    true if the form is valid, false otherwise
   */
  public function bindAndSave($taintedValues, $con = null)
  {
    $this->bind($taintedValues);
    if ($this->isValid())
    {
      $this->save($con);

      return true;
    }

    return false;
  }

  /**
   * Saves the current object to the database.
   *
   * The object saving is done in a transaction and handled by the doSave() method.
   *
   * If the form is not valid, it throws an sfValidatorError.
   *
   * @param Connection An optional Connection object
   *
   * @return BaseObject The current saved object
   *
   * @see doSave()
   */
  public function save($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    try
    {
      $con->begin();

      $this->doSave($con);

      $con->commit();
    }
    catch (Exception $e)
    {
      $con->rollback();

      throw $e;
    }

    return $this->object;
  }

  /**
   * Updates the values of the object with the cleaned up values.
   *
   * @return BaseObject The current updated object
   */
  public function updateObject()
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    $values = $this->getValues();

    // remove special columns that are updated automatically
    unset($values['updated_at'], $values['updated_on'], $values['created_at'], $values['created_on']);

    $this->object->fromArray($values, BasePeer::TYPE_FIELDNAME);

    return $this->object;
  }

  /**
   * Saves the associated i18n objects.
   *
   * @param Connection An optional Connection object
   */
  public function saveI18n($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!$this->isI18n())
    {
      throw new sfException(sprintf('The model "%s" is not internationalized.', $this->getModelName()));
    }

    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $values = $this->getValues();
    $method = sprintf('getCurrent%s', $this->getI18nModelName());
    foreach ($this->cultures as $culture)
    {
      unset($values[$culture]['id'], $values[$culture]['culture']);

      $i18n = $this->object->$method($culture);
      $i18n->fromArray($values[$culture], BasePeer::TYPE_FIELDNAME);
      $i18n->save($con);
    }
  }

  /**
   * Returns true if the current form has some associated i18n objects.
   *
   * @return Boolean true if the current form has some associated i18n objects, false otherwise
   */
  public function isI18n()
  {
    return !is_null($this->getI18nFormClass());
  }

  /**
   * Returns the name of the i18n model.
   *
   * @return string The name of the i18n model
   */
  public function getI18nModelName()
  {
    return null;
  }

  /**
   * Returns the name of the i18n form class.
   *
   * @return string The name of the i18n form class
   */
  public function getI18nFormClass()
  {
    return null;
  }

  /**
   * Updates and saves the current object.
   *
   * If you want to add some logic before saving or save other associated objects,
   * this is the method to override.
   *
   * @param Connection An optional Connection object
   */
  protected function doSave($con = null)
  {
    if (is_null($con))
    {
      $con = $this->getConnection();
    }

    $this->updateObject();

    $this->object->save($con);

    // i18n table
    if ($this->isI18n())
    {
      $this->saveI18n($con);
    }
  }

  /**
   * Updates the default values of the form with the current values of the current object.
   */
  protected function updateDefaultsFromObject()
  {
    // update defaults for the main object
    if ($this->isNew)
    {
      $this->setDefaults(array_merge($this->object->toArray(BasePeer::TYPE_FIELDNAME), $this->getDefaults()));
    }
    else
    {
      $this->setDefaults(array_merge($this->getDefaults(), $this->object->toArray(BasePeer::TYPE_FIELDNAME)));
    }

    // update defaults for i18n
    if ($this->isI18n())
    {
      $method = sprintf('getCurrent%s', $this->getI18nModelName());
      foreach ($this->cultures as $culture)
      {
        if ($this->isNew)
        {
          $this->setDefault($culture, array_merge($this->object->$method($culture)->toArray(BasePeer::TYPE_FIELDNAME), $this->getDefault($culture)));
        }
        else
        {
          $this->setDefault($culture, array_merge($this->getDefault($culture), $this->object->$method($culture)->toArray(BasePeer::TYPE_FIELDNAME)));
        }
      }
    }
  }
}
