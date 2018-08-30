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
            'updated_at' => $this->bigInteger(),
            'user_id' => $this->integer()
        ]);

        $this->createIndex('notification_created_by_indx', 'notification', 'created_by');
        $this->createIndex('notification_updated_by_indx', 'notification', 'updated_by');
    }

    public function down()
    {
        $this->dropIndex('notification_created_by_indx', 'notification');
        $this->dropIndex('notification_updated_by_indx', 'notification');
        $this->dropTable('notification');
    }
}
