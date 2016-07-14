<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `rubric_table`.
 */
class m160711_063831_create_rubric_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('rubric', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->defaultValue(NULL),
            'name' => $this->string(255)->notNull()
        ]);

        $this->addForeignKey(
            'fk-rubric-rubric',
            'rubric',
            'parent_id',
            'rubric',
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
            'fk-rubric-rubric',
            'rubric'
        );

        $this->dropTable('rubric');
    }
}
