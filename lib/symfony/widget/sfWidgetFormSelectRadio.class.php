<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSelectRadio represents radio HTML tags.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormSelectRadio.class.php 5963 2007-11-10 20:29:22Z fabien $
 */
class sfWidgetFormSelectRadio extends sfWidgetForm
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * choices:         An array of possible choices (required)
   *  * label_separator: The separator to use between the input radio and the label
   *  * separator:       The separator to use between each input radio
   *  * formatter:       A callable to call to format the radio choices
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('choices');

    $this->addOption('label_separator', '&nbsp;');
    $this->addOption('separator', "\n");
    $this->addOption('formatter', array($this, 'formatter'));
  }

  /**
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $choices = $this->getOption('choices');
    if ($choices instanceof sfCallable)
    {
      $choices = $choices->call();
    }

    $inputs = array();
    foreach ($choices as $key => $option)
    {
      $attributes = array(
        'name'  => $name,
        'type'  => 'radio',
        'value' => self::escapeOnce($key),
        'id'    => $id = $this->generateId($name.'[]', self::escapeOnce($key)),
      );

      if (strval($key) == strval($value))
      {
        $attributes['checked'] = 'checked';
      }

      $inputs[] = array(
        'input' => $this->renderTag('input', $attributes),
        'label' => $this->renderContentTag('label', $option, array('for' => $id)),
      );
    }

    return call_user_func($this->getOption('formatter'), $inputs);
  }

  public function formatter($inputs)
  {
    $rows = array();
    foreach ($inputs as $input)
    {
      $rows[] = $this->renderContentTag('li', $input['input'].$this->getOption('label_separator').$input['label']);
    }

    return $this->renderContentTag('ul', implode($this->getOption('separator'), $rows), array('class' => 'radio_list'));
  }
}
