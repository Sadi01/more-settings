<?php

use yii\db\Migration;

class m221119_103510_create_table_howdy_setting_selected_option extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%setting_selected_option}}',
            [
                'id' => $this->primaryKey(),
                'setting_value_id' => $this->integer()->unsigned()->notNull(),
                'option_id' => $this->string(50)->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('setting_selected_option_FK_option_id', '{{%setting_selected_option}}', ['option_id']);
        $this->createIndex('unique_setting_value_id_option_id', '{{%setting_selected_option}}', ['setting_value_id', 'option_id'], true);

        $this->addForeignKey(
            'setting_selected_option_FK_setting_value_id',
            '{{%setting_selected_option}}',
            ['setting_value_id'],
            '{{%setting_value}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%setting_selected_option}}');
    }
}
