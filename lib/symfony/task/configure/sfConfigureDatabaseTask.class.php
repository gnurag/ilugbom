<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates a new application.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfConfigureDatabaseTask.class.php 7892 2008-03-14 21:59:38Z fabien $
 */
class sfConfigureDatabaseTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('dsn', sfCommandArgument::REQUIRED, 'The database dsn'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'The environment', 'all'),
      new sfCommandOption('name', null, sfCommandOption::PARAMETER_OPTIONAL, 'The connection name', 'propel'),
      new sfCommandOption('class', null, sfCommandOption::PARAMETER_OPTIONAL, 'The database class name', 'sfPropelDatabase'),
      new sfCommandOption('app', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
    ));

    $this->namespace = 'configure';
    $this->name = 'database';

    $this->briefDescription = 'Configure database DSN';

    $this->detailedDescription = <<<EOF
The [configure:database|INFO] task configures the database DSN
for a project:

  [./symfony configure:database mysql://root@mYsEcret/localhost/dbname|INFO]

By default, the task change the configuration for all environment. If you want
to change the dsn for a specific environment, use the [env|COMMENT] option:

  [./symfony configure:database --env=dev mysql://root/localhost/dbname_test|INFO]

To change the configuration for a specific application, use the [app|COMMENT] option:

  [./symfony configure:database --app=frontend mysql://root/localhost/dbname|INFO]

You can also specify the connection name and the database class name:

  [./symfony configure:database --name=main --class=sfDoctrineDatabase mysql://root/localhost/dbname|INFO]

WARNING: The [propel.ini|COMMENT] file is also updated when you use a [Propel|COMMENT] database
and configure for [all|COMMENT] environments with no [app|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // update databases.yml
    if (!is_null($options['app']))
    {
      $file = sfConfig::get('sf_apps_dir').'/'.$options['app'].'/config/databases.yml';
    }
    else
    {
      $file = sfConfig::get('sf_config_dir').'/databases.yml';
    }

    $config = file_exists($file) ? sfYaml::load($file) : array();

    $config[$options['env']][$options['name']] = array(
      'class' => $options['class'],
      'param' => array_merge(isset($config[$options['env']][$options['name']]['param']) ? $config[$options['env']][$options['name']]['param'] : array(), array('dsn' => $arguments['dsn'])),
    );

    file_put_contents($file, sfYaml::dump($config, 4));

    // update propel.ini
    if (
      is_null($options['app']) &&
      false !== strpos($options['class'], 'Propel') &&
      'all' == $options['env']
    )
    {
      $propelini = sfConfig::get('sf_config_dir').'/propel.ini';
      if (file_exists($propelini))
      {
        $content = file_get_contents($propelini);
        if (preg_match('/^(.+?):\/\//', $arguments['dsn'], $match))
        {
          $content = preg_replace('/^propel\.database(\s*)=(\s*)(.+?)$/m', 'propel.database$1=$2'.$match[1], $content);
          $content = preg_replace('/^propel\.database\.createUrl(\s*)=(\s*)(.+?)$/m', 'propel.database.createUrl$1=$2'.$arguments['dsn'], $content);
          $content = preg_replace('/^propel\.database\.url(\s*)=(\s*)(.+?)$/m', 'propel.database.url$1=$2'.$arguments['dsn'], $content);

          file_put_contents($propelini, $content);
        }
      }
    }
  }
}
