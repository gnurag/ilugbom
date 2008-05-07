<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorChoiceMany validates than an array of values is in the array of the expected values.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorChoiceMany.class.php 7902 2008-03-15 13:17:33Z fabien $
 */
class sfValidatorChoiceMany extends sfValidatorChoice
{
  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    foreach ($values as $value)
    {
      if (!in_array($value, $choices))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    return $values;
  }
}
