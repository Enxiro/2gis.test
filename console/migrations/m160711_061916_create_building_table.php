<?php

use yii\db\Migration;

/**
 * Handles the creation for table `building`.
 */
class m160711_061916_create_building_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('building', [
            'id' => $this->primaryKey(),
            'street' => $this->string()->notNull(),
            'building_number' => $this->string(20)->notNull(),
            'lat' => $this->decimal(10, 8)->notNull(),
            'lng' => $this->decimal(11, 8)->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('building');
    }
}
