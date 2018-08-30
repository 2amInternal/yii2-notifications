<?php

use yii\db\Migration;

class m161004_172028_create_notification_table extends Migration
{
    public function up()
    {
        $this->createTable('notification', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'data' => $this->string()->notNull()->defaultValue('{}'),
            'is_read' => $this->integer()->notNull()->defaultValue(0),
            'is_deleted' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->bigInteger()->notNull(),
            'updated_at' => $this->bigInteger()->null(),
            'user_id' => $this->integer()
        ]);

        $this->createIndex('notification_user_id_indx', 'notification', 'user_id');
    }

    public function down()
    {
        $this->dropIndex('notification_user_id_indx', 'notification');
        $this->dropTable('notification');
    }
}
