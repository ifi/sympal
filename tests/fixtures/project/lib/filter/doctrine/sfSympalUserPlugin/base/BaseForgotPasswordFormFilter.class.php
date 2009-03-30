<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * ForgotPassword filter form base class.
 *
 * @package    filters
 * @subpackage ForgotPassword *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseForgotPasswordFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'user_id'    => new sfWidgetFormDoctrineChoice(array('model' => 'User', 'add_empty' => true)),
      'unique_key' => new sfWidgetFormFilterInput(),
      'expires_at' => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'user_id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'User', 'column' => 'id')),
      'unique_key' => new sfValidatorPass(array('required' => false)),
      'expires_at' => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('forgot_password_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ForgotPassword';
  }

  public function getFields()
  {
    return array(
      'id'         => 'Number',
      'user_id'    => 'ForeignKey',
      'unique_key' => 'Text',
      'expires_at' => 'Date',
    );
  }
}