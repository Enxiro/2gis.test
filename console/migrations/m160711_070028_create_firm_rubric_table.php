<?php

use yii\db\Migration;

/**
 * Handles the creation for table `firm_rubric_table`.
 */
class m160711_070028_create_firm_rubric_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('firm_rubric', [
            'firm_id' => $this->integer()->notNull(),
            'rubric_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk-firm_rubric',
            'firm_rubric',
            ['firm_id', 'rubric_id']
        );

        $this->addForeignKey(
            'fk-firm_rubric-firm',
            'firm_rubric',
            'firm_id',
            'firm',
            'id',
            'RESTRICT'
        );

        $this->addForeignKey(
            'fk-firm_rubric-rubric',
            'firm_rubric',
            'rubric_id',
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
            'fk-firm_rubric-rubric',
            'firm_rubric'
        );

        $this->dropForeignKey(
            'fk-firm_rubric-firm',
            'firm_rubric'
        );

        $this->dropTable('firm_rubric');
    }
}
