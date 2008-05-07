<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for all tasks.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTask.class.php 8305 2008-04-04 18:28:02Z fabien $
 */
abstract class sfTask
{
  protected
    $namespace           = '',
    $name                = null,
    $aliases             = array(),
    $briefDescription    = '',
    $detailedDescription = '',
    $arguments           = array(),
    $options             = array(),
    $dispatcher          = null,
    $formatter           = null;

  /**
   * Constructor.
   *
   * @param sfEventDispatcher A sfEventDispatcher instance
   * @param sfFormatter       A sfFormatter instance
   */
  public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->initialize($dispatcher, $formatter);

    $this->configure();
  }

  /**
   * Initializes the sfTask instance.
   *
   * @param sfEventDispatcher A sfEventDispatcher instance
   * @param sfFormatter       A sfFormatter instance
   */
  public function initialize(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter  = $formatter;
  }

  /**
   * Configures the current task.
   */
  protected function configure()
  {
  }

  /**
   * Runs the task from the CLI.
   *
   * @param sfCommandManager A sfCommandManager instance
   * @param mixed            The command line options
   */
  public function runFromCLI(sfCommandManager $commandManager, $options = null)
  {
    $commandManager->getArgumentSet()->addArguments($this->getArguments());
    $commandManager->getOptionSet()->addOptions($this->getOptions());

    return $this->doRun($commandManager, $options);
  }

  /**
   * Runs the task.
   *
   * @param array An array of arguments
   * @param array An array of options
   */
  public function run($arguments = array(), $options = array())
  {
    $commandManager = new sfCommandManager(new sfCommandArgumentSet($this->getArguments()), new sfCommandOptionSet($this->getOptions()));

    // add -- before each option if needed
    foreach ($options as &$option)
    {
      if (0 !== strpos($option, '--'))
      {
        $option = '--'.$option;
      }
    }

    return $this->doRun($commandManager, implode(' ', array_merge($arguments, $options)));
  }

  /**
   * Returns the argument objects.
   *
   * @return sfCommandArgument An array of sfCommandArgument objects.
   */
  public function getArguments()
  {
    return $this->arguments;
  }

  /**
   * Adds an array of argument objects.
   *
   * @return sfCommandArgument An array of sfCommandArgument objects.
   */
  public function addArguments($arguments)
  {
    $this->arguments = array_merge($this->arguments, $arguments);
  }

  /**
   * Add an argument.
   *
   * This method always use the sfCommandArgument class to create an option.
   *
   * @see sfCommandArgument::__construct()
   */
  public function addArgument($name, $mode = null, $help = '', $default = null)
  {
    $this->arguments[] = new sfCommandArgument($name, $mode, $help, $default);
  }

  /**
   * Returns the options objects.
   *
   * @return sfCommandOption An array of sfCommandOption objects.
   */
  public function getOptions()
  {
    return $this->options;
  }

  /**
   * Adds an array of option objects.
   *
   * @return sfCommandOption An array of sfCommandOption objects.
   */
  public function addOptions($options)
  {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * Add an option.
   *
   * This method always use the sfCommandOption class to create an option.
   *
   * @see sfCommandOption::__construct()
   */
  public function addOption($name, $shortcut = null, $mode = null, $help = '', $default = null)
  {
    $this->options[] = new sfCommandOption($name, $shortcut, $mode, $help, $default);
  }

  /**
   * Returns the task namespace.
   *
   * @param string The task namespace
   */
  public function getNamespace()
  {
    return $this->namespace;
  }

  /**
   * Returns the task name
   *
   * @return string The task name
   */
  public function getName()
  {
    if ($this->name)
    {
      return $this->name;
    }

    $name = get_class($this);

    if ('sf' == substr($name, 0, 2))
    {
      $name = substr($name, 2);
    }

    if ('Task' == substr($name, -4))
    {
      $name = substr($name, 0, -4);
    }

    return str_replace('_', '-', sfInflector::underscore($name));
  }

  /**
   * Returns the fully qualified task name.
   *
   * @return string The fully qualified task name
   */
  final function getFullName()
  {
    return $this->getNamespace() ? $this->getNamespace().':'.$this->getName() : $this->getName();
  }

  /**
   * Returns the brief description for the task.
   *
   * @return string The brief description for the task
   */
  public function getBriefDescription()
  {
    return $this->briefDescription;
  }

  /**
   * Returns the detailed description for the task.
   *
   * It also formats special string like [...|COMMENT]
   * depending on the current formatter.
   *
   * @return string The detailed description for the task
   */
  public function getDetailedDescription()
  {
    return preg_replace('/\[(.+?)\|(\w+)\]/se', '$this->formatter->format("$1", "$2")', $this->detailedDescription);
  }

  /**
   * Returns the aliases for the task.
   *
   * @return array An array of aliases for the task
   */
  public function getAliases()
  {
    return $this->aliases;
  }

  /**
   * Returns the synopsis for the task.
   *
   * @param string The synopsis
   */
  public function getSynopsis()
  {
    $options = array();
    foreach ($this->getOptions() as $option)
    {
      $shortcut = $option->getShortcut() ? sprintf('|-%s', $option->getShortcut()) : '';
      $options[] = sprintf('['.($option->isParameterRequired() ? '--%s%s="..."' : ($option->isParameterOptional() ? '--%s%s[="..."]' : '--%s%s')).']', $option->getName(), $shortcut);
    }

    $arguments = array();
    foreach ($this->getArguments() as $argument)
    {
      $arguments[] = sprintf($argument->isRequired() ? '%s' : '[%s]', $argument->getName().($argument->isArray() ? '1' : ''));

      if ($argument->isArray())
      {
        $arguments[] = '... [nameN]';
      }
    }

    return sprintf('%%s %s %s %s', $this->getFullName(), implode(' ', $options), implode(' ', $arguments));
  }

  protected function process(sfCommandManager $commandManager, $options)
  {
    $commandManager->process($options);
    if (!$commandManager->isValid())
    {
      throw new sfCommandArgumentsException(sprintf("The execution of task \"%s\" failed.\n- %s", $this->getFullName(), implode("\n- ", $commandManager->getErrors())));
    }
  }

  protected function doRun(sfCommandManager $commandManager, $options)
  {
    $this->dispatcher->filter(new sfEvent($this, 'command.filter_options', array('command_manager' => $commandManager)), $options);

    $this->process($commandManager, $options);

    $event = new sfEvent($this, 'command.pre_command', array('arguments' => $commandManager->getArgumentValues(), 'options' => $commandManager->getOptionValues()));
    $this->dispatcher->notifyUntil($event);
    if ($event->isProcessed())
    {
      return $this->getReturnValue();
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent($this, 'command.post_command'));

    return $ret;
  }

  /**
   * Logs a message.
   *
   * @param mixed The message as an array of lines of a single string
   */
  public function log($messages)
  {
    if (!is_array($messages))
    {
      $messages = array($messages);
    }

    $this->dispatcher->notify(new sfEvent($this, 'command.log', $messages));
  }

  /**
   * Logs a message in a section.
   *
   * @param string  The section name
   * @param string  The message
   * @param integer The maximum size of a line
   */
  public function logSection($section, $message, $size = null)
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size))));
  }

  /**
   * Executes the current task.
   *
   * @param array An array of arguments
   * @param array An array of options
   */
   abstract protected function execute($arguments = array(), $options = array());
}
