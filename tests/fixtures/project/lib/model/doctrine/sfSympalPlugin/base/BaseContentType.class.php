<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseContentType extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('content_type');
        $this->hasColumn('id', 'integer', 4, array('type' => 'integer', 'primary' => true, 'autoincrement' => true, 'length' => '4'));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('label', 'string', 255, array('type' => 'string', 'notnull' => true, 'length' => '255'));
        $this->hasColumn('list_path', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('view_path', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('layout', 'string', 255, array('type' => 'string', 'length' => '255'));
    }

    public function setUp()
    {
        $this->hasMany('MenuItem as MenuItems', array('local' => 'id',
                                                      'foreign' => 'content_type_id'));

        $this->hasMany('Content', array('local' => 'id',
                                        'foreign' => 'content_type_id'));

        $this->hasMany('ContentTemplate as Templates', array('local' => 'id',
                                                             'foreign' => 'content_type_id'));

        $sluggable0 = new Doctrine_Template_Sluggable(array('fields' => array(0 => 'name')));
        $i18n0 = new Doctrine_Template_I18n(array('fields' => array(0 => 'label')));
        $this->actAs($sluggable0);
        $this->actAs($i18n0);
    }
}