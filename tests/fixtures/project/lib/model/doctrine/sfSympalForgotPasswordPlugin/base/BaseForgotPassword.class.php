<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseForgotPassword extends sfSympalDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('forgot_password');
        $this->hasColumn('user_id', 'integer', 4, array('type' => 'integer', 'notnull' => true, 'length' => '4'));
        $this->hasColumn('unique_key', 'string', 255, array('type' => 'string', 'length' => '255'));
        $this->hasColumn('expires_at', 'timestamp', null, array('type' => 'timestamp', 'notnull' => true));
    }

    public function setUp()
    {
        $this->hasOne('sfGuardUser as User', array('local' => 'user_id',
                                                   'foreign' => 'id',
                                                   'onDelete' => 'CASCADE'));
    }
}