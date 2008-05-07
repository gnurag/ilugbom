<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAnsiColorFormatter provides methods to colorize text to be displayed on a console.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfAnsiColorFormatter.class.php 5250 2007-09-24 08:11:50Z fabien $
 */
class sfAnsiColorFormatter extends sfFormatter
{
  protected
    $styles = array(
      'ERROR'   => array('bg' => 'red', 'fg' => 'white', 'bold' => true),
      'INFO'    => array('fg' => 'green', 'bold' => true),
      'COMMENT' => array('fg' => 'yellow'),
    ),
    $options    = array('bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8),
    $foreground = array('black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37),
    $background = array('black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47);

  /**
   * Sets a new style.
   *
   * @param string The style name
   * @param array  An array of options
   */
  public function setStyle($name, $options = array())
  {
    $this->styles[$name] = $options;
  }

  /**
   * Formats a text according to the given style or parameters.
   *
   * @param  string The test to style
   * @param  mixed  An array of options or a style name
   *
   * @return string The styled text
   */
  public function format($text = '', $parameters = array(), $stream = STDOUT)
  {
    if (!$this->supportsColors($stream))
    {
      return $text;
    }

    if (!is_array($parameters) && 'NONE' == $parameters)
    {
      return $text;
    }

    if (!is_array($parameters) && isset($this->styles[$parameters]))
    {
      $parameters = $this->styles[$parameters];
    }

    $codes = array();
    if (isset($parameters['fg']))
    {
      $codes[] = $this->foreground[$parameters['fg']];
    }
    if (isset($parameters['bg']))
    {
      $codes[] = $this->background[$parameters['bg']];
    }
    foreach ($this->options as $option => $value)
    {
      if (isset($parameters[$option]) && $parameters[$option])
      {
        $codes[] = $value;
      }
    }

    return "\033[".implode(';', $codes).'m'.$text."\033[0m";
  }

  /**
   * Formats a message within a section.
   *
   * @param string  The section name
   * @param string  The text message
   * @param integer The maximum size allowed for a line (65 by default)
   */
  public function formatSection($section, $text, $size = null)
  {
    $width = 9 + strlen($this->format('', 'INFO'));

    return sprintf(">> %-${width}s %s", $this->format($section, 'INFO'), $this->excerpt($text, $size));
  }

  /**
   * Truncates a line.
   *
   * @param string  The text
   * @param integer The maximum size of the returned string (65 by default)
   *
   * @return string The truncated string
   */
  public function excerpt($text, $size = null)
  {
    if (!$size)
    {
      $size = $this->size;
    }

    if (strlen($text) < $size)
    {
      return $text;
    }

    $subsize = floor(($size - 3) / 2);

    return substr($text, 0, $subsize).$this->format('...', 'INFO').substr($text, -$subsize);
  }

  /**
   * Returns true if the stream supports colorization.
   *
   * Colorization is disabled if not supported by the stream:
   *
   *  -  windows
   *  -  non tty consoles
   *
   * @param  mixed A stream
   *
   * @return Boolean true if the stream supports colorization, false otherwise
   */
  public function supportsColors($stream)
  {
    return DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty($stream);
  }
}
