<?php

use yii\db\Migration;

class m221119_103509_create_table_howdy_setting extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%setting}}',
            [
                'id' => $this->primaryKey()->unsigned(),
                'cat_id' => $this->integer()->unsigned()->notNull(),
                'name' => $this->string(100)->notNull(),
                'description' => $this->string(200),
                'custom_data_source' => $this->string(150),
                'custom_validation_rule' => $this->string(150),
                'skip_custom_validation_on_empty' => $this->boolean()->notNull()->defaultValue('1'),
                'status' => $this->tinyInteger()->notNull(),
                'type' => $this->smallInteger()->notNull(),
                'label' => $this->string(100)->notNull(),
                'place_holder' => $this->string(100),
                'helper_text' => $this->string(200),
                'default_value' => $this->string(100),
                'required' => $this->boolean()->notNull()->defaultValue('0'),
                'max_length' => $this->integer(),
                'min_length' => $this->integer(),
                'max' => $this->integer(),
                'min' => $this->integer(),
                'apply_separator' => $this->boolean()->notNull()->defaultValue('0'),
                'max_size' => $this->integer(),
                'calendar_type' => $this->boolean()->defaultValue('1'),
                'number_type' => $this->boolean()->defaultValue('1'),
                'direction' => $this->tinyInteger()->notNull()->defaultValue('1'),
                'created_by' => $this->integer()->unsigned()->notNull(),
                'updated_by' => $this->integer()->unsigned()->notNull(),
                'created_at' => $this->integer()->unsigned()->notNull(),
                'updated_at' => $this->integer()->unsigned()->notNull(),
                'is_deleted' => $this->boolean()->notNull()->defaultValue('0'),
                'deleted_at' => $this->integer()->unsigned(),
            ],
            $tableOptions
        );

        $this->createIndex('settings_FK_created_by', '{{%setting}}', ['created_by']);
        $this->createIndex('settings_FK_updated_by', '{{%setting}}', ['updated_by']);
        $this->createIndex('unique_name_cat_id', '{{%setting}}', ['cat_id', 'name', 'is_deleted', 'deleted_at'], true);

        $this->addForeignKey(
            'settings_FK_cat_id',
            '{{%setting}}',
            ['cat_id'],
            '{{%setting_cat}}',
            ['id'],
            'NO ACTION',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%setting}}');
    }
}
