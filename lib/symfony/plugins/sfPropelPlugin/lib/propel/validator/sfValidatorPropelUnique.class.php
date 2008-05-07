<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorPropelUnique validates that the uniqueness of a column.
 *
 * Warning: sfValidatorPropelUnique is susceptible to race conditions.
 * To avoid this issue, wrap the validation process and the model saving
 * inside a transaction.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorPropelUnique.class.php 8807 2008-05-06 14:12:28Z fabien $
 */
class sfValidatorPropelUnique extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param array  An array of options
   * @param array  An array of error messages
   *
   * @see sfValidatorSchema
   */
  public function __construct($options = array(), $messages = array())
  {
    parent::__construct(null, $options, $messages);
  }

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:       The model class (required)
   *  * column:      The unique column name in Propel field name format (required)
   *                 If the uniquess is for several columns, you can pass an array of field names
   *  * primary_key: The primary key column name in Propel field name format (optional, will be introspected if not provided)
   *                 You can also pass an array if the table has several primary keys
   *  * connection:  The Propel connection to use (null by default)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addRequiredOption('column');
    $this->addOption('primary_key', null);
    $this->addOption('connection', null);

    $this->setMessage('invalid', 'An object with the same "%column%" already exist.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    if (!is_array($this->getOption('column')))
    {
      $this->setOption('column', array($this->getOption('column')));
    }

    $criteria = new Criteria();
    foreach ($this->getOption('column') as $column)
    {
      $colName = call_user_func(array($this->getOption('model').'Peer', 'translateFieldName'), $column, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);

      $criteria->add($colName, $values[$column]);
    }

    $object = call_user_func(array($this->getOption('model').'Peer', 'doSelectOne'), $criteria, $this->getOption('connection'));

    // if no object or if we're updating the object, it's ok
    if (is_null($object) || $this->isUpdate($object, $values))
    {
      return $values;
    }

    throw new sfValidatorError($this, 'invalid', array('column' => implode(', ', $this->getOption('column'))));
  }

  /**
   * Returns whether the object is being updated.
   *
   * @param BaseObject  A Propel object
   * @param array       An array of values
   *
   * @param Boolean     true if the object is being updated, false otherwise
   */
  protected function isUpdate(BaseObject $object, $values)
  {
    // check each primary key column
    foreach ($this->getPrimaryKeys() as $column)
    {
      $columnPhpName = call_user_func(array($this->getOption('model').'Peer', 'translateFieldName'), $column, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME);
      $method = 'get'.$columnPhpName;
      if ($object->$method() != $values[$column])
      {
        return false;
      }
    }

    return true;
  }

  /**
   * Returns the primary keys for the model.
   *
   * @return array An array of primary keys
   */
  protected function getPrimaryKeys()
  {
    if (is_null($this->getOption('primary_key')))
    {
      $primaryKeys = array();
      $tableMap = call_user_func(array($this->getOption('model').'Peer', 'getTableMap'));
      foreach ($tableMap->getColumns() as $column)
      {
        if (!$column->isPrimaryKey())
        {
          continue;
        }

        $primaryKeys[] = call_user_func(array($this->getOption('model').'Peer', 'translateFieldName'), $column->getPhpName(), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_FIELDNAME);
      }

      $this->setOption('primary_key', $primaryKeys);
    }

    if (!is_array($this->getOption('primary_key')))
    {
      $this->setOption('primary_key', array($this->getOption('primary_key')));
    }

    return $this->getOption('primary_key');
  }
}
