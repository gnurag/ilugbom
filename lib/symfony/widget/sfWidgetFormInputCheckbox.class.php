<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInputCheckbox represents an HTML checkbox tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormInputCheckbox.class.php 8487 2008-04-16 21:07:17Z fabien $
 */
class sfWidgetFormInputCheckbox extends sfWidgetFormInput
{
  /**
   * @see sfWidgetFormInput
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'checkbox');
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if (!is_null($value))
    {
      $attributes['checked'] = 'checked';
    }

    return parent::render($name, null, $attributes, $errors);
  }
}
