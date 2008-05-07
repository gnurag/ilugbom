<?php

# FROZEN_SF_LIB_DIR: /var/www/production/sfweb/symfony-for-release/lib

require_once dirname(__FILE__).'/../lib/symfony/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
  }
}
