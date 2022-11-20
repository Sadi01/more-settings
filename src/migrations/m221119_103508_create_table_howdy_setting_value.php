<?php

use yii\db\Migration;

class m221119_103508_create_table_howdy_setting_value extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%setting_value}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'setting_id' => $this->integer()->unsigned()->notNull(),
                'model_id' => $this->integer()->unsigned(),
                'source_model_id' => $this->integer(),
                'value' => $this->text(),
                'created_by' => $this->integer()->unsigned()->notNull(),
                'updated_by' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->integer()->unsigned()->notNull(),
                'updated_at' => $this->integer()->unsigned()->notNull(),
            ],
            $tableOptions
        );

        $this->createIndex('setting_value_FK_created_by', '{{%setting_value}}', ['created_by']);
        $this->createIndex('setting_value_FK_updated_by', '{{%setting_value}}', ['updated_by']);
        $this->createIndex('unique_model_id_setting_id', '{{%setting_value}}', ['setting_id', 'model_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%setting_value}}');
    }
}
