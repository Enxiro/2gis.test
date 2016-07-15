<?php

namespace console\models;

use Yii;
use api\modules\v1\models\Phone;
use api\modules\v1\models\Firm;
use api\modules\v1\models\Rubric;
use api\modules\v1\models\Building;
use api\modules\v1\models\FirmRubric;
use yii\base\ErrorException;
use yii\base\Exception;

class DataGenerator extends \yii\base\Model
{
    const BUILDINGS_TO_BE_GENERATED = 1000;
    const FIRMS_TO_BE_GENERATED = 10000;

    const FAKER_SEED = 1234;
    const FAKER_LOCALE = 'ru_RU';

    const BATCH_LIMIT = 100;

    private $faker;

    /**
     *
     * @var bool
     */
    private $skipValidation = false;

    /**
     * Add rubrics from special array using recursion
     *
     * @param $rubricsArr
     * @param int $parentId
     * @param $inserted
     * @throws \Exception
     */
    private function addRubricsRecursion($rubricsArr, $parentId = null, &$inserted)
    {
        foreach($rubricsArr as $rubric) {
            $model = new Rubric();
            $model->parent_id = $parentId;
            $model->name = $rubric[0];
            if ($model->save()) {
                $inserted++;

                if (!empty($rubric[1])) {
                    $this->addRubricsRecursion($rubric[1], $model->id, $inserted);
                }
            } else {
                throw new \Exception('Error! Rubric not inserted!'); // TODO
            }
        }
    }

    /**
     * Init
     */
    public function init()
    {
        parent::init();
        $this->faker = \Faker\Factory::create( self::FAKER_LOCALE );
        $this->faker->seed( self::FAKER_SEED );
        srand(self::FAKER_SEED);
    }

    /**
     * Delete all data from DB
     */
    public function deleteAllData()
    {
        Phone::deleteAll();
        FirmRubric::deleteAll();
        Firm::deleteAll();
        Rubric::deleteAll();
        Building::deleteAll();

        // TODO Reset AI?
    }

    /**
     * Generate buildings
     *
     * @return int inserted buildings count
     * @throws Exception
     */
    public function generateBuildings()
    {
        $inserted = 0;

        $rows = [];
        $batchCounter = 0;
        $building = new Building();

        for($i = 0; $i < self::BUILDINGS_TO_BE_GENERATED; $i++) {
            $building->street = $this->faker->streetName;
            $building->building_number = $this->faker->buildingNumber;
            $building->lat = $this->faker->latitude;
            $building->lng = $this->faker->longitude;
            if ( !$this->skipValidation && $building->validate() == false ) {
                throw new Exception('Error! Building not inserted'); // TODO
            }

            $rows[] = $building->attributes;
            $batchCounter++;
            $inserted++;

            if ($batchCounter >= self::BATCH_LIMIT || $i + 1 == self::BUILDINGS_TO_BE_GENERATED) {
                Yii::$app->db->createCommand()->batchInsert(Building::tableName(), $building->attributes(), $rows)->execute();
                $batchCounter = 0;
                $rows = [];
            }
        }

        return $inserted;
    }

    /**
     * Add rubrics data from special prepared array
     *
     * @return int inserted rubrics count
     * @throws \Exception
     */
    public function generateRubrics()
    {
        $rubricsArr = [
            ['Еда', [
                ['Полуфабрикаты оптом'],
                ['Мясная продукция'],
                ['Шаурмяшечные']
            ]],
            ['Автомобили', [
                ['Грузовые'],
                ['Легковые', [
                    ['Запчасти для подвески'],
                    ['Шины/Диски']
                ]]
            ]],
            ['Спорт', [
                ['Метание бобра'],
                ['Прыжки с куницей'],
                ['Перетягивание удава']
            ]]
        ];

        $inserted = 0;

        $this->addRubricsRecursion($rubricsArr, null, $inserted);

        return $inserted;
    }

    /**
     * Generate firms data with phones and link firm to rubric
     *
     * @return array
     * @throws \Exception
     */
    public function generateFirms()
    {
        // -------------------------------
        // -- Insert firms
        // -------------------------------
        $insertedFirms = 0;

        /**
         * Previously we added buildings one by one, therefore buildings Ids is going one by one.
         * So we can get min and max buildings Ids, and after that we can get random id of exist
         * building without using DB
         */
        $minBuildingId = (new \yii\db\Query())->from('building')->min('id');
        $maxBuildingId = (new \yii\db\Query())->from('building')->max('id');

        $rows = [];
        $batchCounter = 0;
        $firm = new Firm();

        for ($i = 0; $i < self::FIRMS_TO_BE_GENERATED; $i++) {
            // Create firm
            $firm->name = $this->faker->company();
            $firm->building_id = rand($minBuildingId, $maxBuildingId);
            if ( !$this->skipValidation && $firm->validate() == false) {
                throw new \Exception('Error! Firm not inserted!'); // TODO
            }
            $rows[] = $firm->attributes;
            $insertedFirms++;
            $batchCounter++;

            if ($batchCounter > self::BATCH_LIMIT || $i + 1 == self::FIRMS_TO_BE_GENERATED) {
                Yii::$app->db->createCommand()->batchInsert(Firm::tableName(), $firm->attributes(), $rows)->execute();
                $batchCounter = 0;
                $rows = [];
            }
        }

        // -------------------------------
        // -- Insert FirmRubric
        // -------------------------------
        $insertedFirmRubrics = 0;

        $minFirmId = (new \yii\db\Query())->from('firm')->min('id');
        $maxFirmId = (new \yii\db\Query())->from('firm')->max('id');
        $minRubricId = (new \yii\db\Query())->from('rubric')->min('id');
        $maxRubricId = (new \yii\db\Query())->from('rubric')->max('id');

        $rows = [];
        $batchCounter = 0;

        $firmRubric = new FirmRubric();
        for ($firmId = $minFirmId; $firmId <= $maxFirmId; $firmId++) {
            // Add rubrics
            // TODO add all rubrics from tree
            $firmRubric->firm_id = $firmId;
            $firmRubric->rubric_id = rand($minRubricId, $maxRubricId);
            if ( !$this->skipValidation && $firmRubric->validate() == false) {
                throw new \Exception('Error! FirmRubric not inserted!'); // TODO
            }
            $rows[] = $firmRubric->attributes;
            $batchCounter++;
            $insertedFirmRubrics++;

            if ($batchCounter > self::BATCH_LIMIT || $firmId == $maxFirmId) {
                Yii::$app->db->createCommand()->batchInsert(FirmRubric::tableName(), $firmRubric->attributes(), $rows)->execute();
                $batchCounter = 0;
                $rows = [];
            }
        }

        // -------------------------------
        // -- Insert Phones
        // -------------------------------
        $insertedPhones = 0;
        $rows = [];
        $batchCounter = 0;

        $phone = new Phone();
        for ($firmId = $minFirmId; $firmId <= $maxFirmId; $firmId++) {
            // Generate 1,2 or 3 phone numbers
            $currFirmPhonesCount = rand(1, 3);
            do {
                $phone->firm_id = $firmId;
                $phone->type = rand(1,2) * 10;
                // TODO phones will be unique
                if ($phone->type == Phone::TYPE_MOBILE) {
                    $phone->phone = $this->faker->numerify('89#########');
                } else {
                    $phone->phone = $this->faker->numerify('3######');
                }
                if ( !$this->skipValidation && $phone->validate() == false ) {
                    throw new \Exception('Error! Phone not inserted!'); // TODO
                }

                $currFirmPhonesCount--;
                $insertedPhones++;

                $rows[] = $phone->attributes;
                $batchCounter++;
            } while ($currFirmPhonesCount > 0);

            if ($batchCounter > self::BATCH_LIMIT || $firmId == $maxFirmId) {
                Yii::$app->db->createCommand()->batchInsert(Phone::tableName(), $phone->attributes(), $rows)->execute();
                $batchCounter = 0;
                $rows = [];
            }
        }

        return [
            'insertedPhones' => $insertedPhones,
            'insertedFirms' => $insertedFirms,
            'insertedFirmRubrics' => $insertedFirmRubrics
        ];
    }
}