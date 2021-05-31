<?php

use yii\db\Migration;

/**
 * Class m210531_063930_default_value
 */
class m210531_063930_default_value extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%default_value}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'google_file_link' => $this->text(),
            'yandex_file_path' => $this->text(),
            'general_message_template' => $this->text(),
            'message_template' => $this->text(),
            'user' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("default_value_user_fk", "{{%default_value}}",
            "user", "{{%user}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%default_value}}');
    }
}