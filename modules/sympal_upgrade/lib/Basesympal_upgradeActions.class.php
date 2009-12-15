<?php

/**
 * Base actions for the sfSympalPlugin sympal_upgrade module.
 * 
 * @package     sfSympalPlugin
 * @subpackage  sympal_upgrade
 * @author      Your name here
 * @version     SVN: $Id: BaseActions.class.php 12534 2008-11-01 13:38:27Z Kris.Wallsmith $
 */
abstract class Basesympal_upgradeActions extends sfActions
{
  public function preExecute()
  {
    parent::preExecute();

    $this->useAdminLayout();

    $this->upgrade = new sfSympalUpgradeFromWeb(
      $this->getContext()->getConfiguration(),
      $this->getContext()->getEventDispatcher(),
      new sfFormatter()
    );
    $this->hasNewVersion = $this->upgrade->hasNewVersion();
    $this->latestVersion = $this->upgrade->getLatestVersion();
    $this->currentVersion = $this->upgrade->getCurrentVersion();
  }

  public function executeCheck(sfWebRequest $request)
  {
    $this->commands = $this->upgrade->getUpgradeCommands();
  }
}