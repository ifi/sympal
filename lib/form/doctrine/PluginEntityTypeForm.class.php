<?php

/**
 * PluginContentType form.
 *
 * @package    form
 * @subpackage ContentType
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 6174 2007-11-27 06:22:40Z jwage $
 */
abstract class PluginContentTypeForm extends BaseContentTypeForm
{
  public function setup()
  {
    parent::setup();

    sfSympalTools::changeLayoutWidget($this);
    sfSympalTools::embedI18n('ContentType', $this);
  }
}