<?php

use yii\db\Migration;

class m221119_103506_create_table_howdy_setting_cat extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%setting_cat}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'title' => $this->string(100)->notNull(),
                'description' => $this->string(150),
                'model_class' => $this->string(),
                'source_model_class' => $this->string(),
                'is_public' => $this->boolean()->notNull()->defaultValue('0'),
                'is_deleted' => $this->boolean()->notNull()->defaultValue('0'),
                'deleted_at' => $this->integer()->unsigned(),
            ],
            $tableOptions
        );

        $this->createIndex('unique_title_model_class', '{{%setting_cat}}', ['title', 'model_class', 'is_deleted', 'deleted_at'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%setting_cat}}');
    }
}
