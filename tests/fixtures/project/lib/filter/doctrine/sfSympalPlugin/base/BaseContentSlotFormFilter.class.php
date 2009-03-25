<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/BaseFormFilterDoctrine.class.php');

/**
 * ContentSlot filter form base class.
 *
 * @package    filters
 * @subpackage ContentSlot *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseContentSlotFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'content_id'           => new sfWidgetFormDoctrineChoice(array('model' => 'Content', 'add_empty' => true)),
      'content_slot_type_id' => new sfWidgetFormDoctrineChoice(array('model' => 'ContentSlotType', 'add_empty' => true)),
      'name'                 => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'content_id'           => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Content', 'column' => 'id')),
      'content_slot_type_id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'ContentSlotType', 'column' => 'id')),
      'name'                 => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('content_slot_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ContentSlot';
  }

  public function getFields()
  {
    return array(
      'id'                   => 'Number',
      'content_id'           => 'ForeignKey',
      'content_slot_type_id' => 'ForeignKey',
      'name'                 => 'Text',
    );
  }
}