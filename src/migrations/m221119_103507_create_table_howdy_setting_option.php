<?php

use yii\db\Migration;

class m221119_103507_create_table_howdy_setting_option extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%setting_option}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'setting_id' => $this->integer()->unsigned()->notNull(),
                'option_key' => $this->string(50)->notNull(),
                'name' => $this->string(150)->notNull(),
                'description' => $this->string(200),
                'order_id' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('option_key', '{{%setting_option}}', ['option_key']);
        $this->createIndex('unique_name_setting_id', '{{%setting_option}}', ['setting_id', 'name'], true);
        $this->createIndex('unique_setting_option_key', '{{%setting_option}}', ['setting_id', 'option_key'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%setting_option}}');
    }
}
