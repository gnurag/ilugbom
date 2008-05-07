<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nChoiceLanguage validates than the value is a valid language.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfValidatorI18nChoiceLanguage.class.php 7274 2008-02-02 18:50:47Z fabien $
 */
class sfValidatorI18nChoiceLanguage extends sfValidatorChoice
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * culture:   The culture to use for internationalized strings (required)
   *  * languages: An array of language codes to use (ISO 639-1)
   *
   * @see sfValidatorChoice
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('culture');
    $this->addOption('languages');

    // populate choices with all languages
    $culture = isset($options['culture']) ? $options['culture'] : 'en';

    $cultureInfo = new sfCultureInfo($culture);
    $languages = array_keys($cultureInfo->getLanguages());

    // restrict languages to a sub-set
    if (isset($options['languages']))
    {
      if ($problems = array_diff($options['languages'], $languages))
      {
        throw new InvalidArgumentException(sprintf('The following languages do not exist: %s.', implode(', ', $problems)));
      }

      $languages = $options['languages'];
    }

    sort($languages);

    $this->setOption('choices', $languages);
  }
}
