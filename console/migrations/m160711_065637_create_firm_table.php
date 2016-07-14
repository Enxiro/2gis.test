<?php

use yii\db\Migration;

/**
 * Handles the creation for table `firm_table`.
 */
class m160711_065637_create_firm_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('firm', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'building_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey(
            'fk-building-firm',
            'firm',
            'building_id',
            'building',
            'id',
            'RESTRICT'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey(
            'fk-building-firm',
            'firm'
        );

        $this->dropTable('firm');
    }
}
