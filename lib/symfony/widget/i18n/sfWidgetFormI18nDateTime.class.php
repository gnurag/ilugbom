<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nDateTime represents a date and time widget.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormI18nDateTime.class.php 6341 2007-12-06 16:18:16Z fabien $
 */
class sfWidgetFormI18nDateTime extends sfWidgetFormDateTime
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * culture: The culture to use for internationalized strings (required)
   *
   * @see sfWidgetFormDateTime
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('culture');

    $culture = isset($options['culture']) ? $options['culture'] : 'en';

    // format
    $this->setOption('format', str_replace(array('{0}', '{1}'), array('%time%', '%date%'), sfDateTimeFormatInfo::getInstance($culture)->getDateTimeOrderPattern()));
  }

  /**
   * @see sfWidgetFormDateTime
   */
  protected function getDateWidget()
  {
    return new sfWidgetFormI18nDate(array_merge(array('culture' => $this->getOption('culture')), $this->getOptionsFor('date')), $this->getAttributesFor('date'));
  }

  /**
   * @see sfWidgetFormDateTime
   */
  protected function getTimeWidget()
  {
    return new sfWidgetFormI18nTime(array_merge(array('culture' => $this->getOption('culture')), $this->getOptionsFor('time')), $this->getAttributesFor('time'));
  }
}
