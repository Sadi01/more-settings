<?php

use yii\db\Migration;

class m221119_103511_create_foreign_keys extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey(
            'setting_option_FK_setting_id',
            '{{%setting_option}}',
            ['setting_id'],
            '{{%setting}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'setting_value_FK_setting_id',
            '{{%setting_value}}',
            ['setting_id'],
            '{{%setting}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'settings_FK_created_by',
            '{{%setting}}',
            ['created_by'],
            '{{%user}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'settings_FK_updated_by',
            '{{%setting}}',
            ['updated_by'],
            '{{%user}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'setting_selected_option_FK_option_id',
            '{{%setting_selected_option}}',
            ['option_id'],
            '{{%setting_option}}',
            ['option_key'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('setting_selected_option_FK_option_id', '{{%setting_selected_option}}');
        $this->dropForeignKey('settings_FK_updated_by', '{{%setting}}');
        $this->dropForeignKey('settings_FK_created_by', '{{%setting}}');
        $this->dropForeignKey('setting_value_FK_setting_id', '{{%setting_value}}');
        $this->dropForeignKey('setting_option_FK_setting_id', '{{%setting_option}}');
    }
}