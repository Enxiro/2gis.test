<?php

namespace console\controllers;

use console\models\DataGenerator;
use Yii;
use yii\helpers\Console;
use yii\web\Controller;

//use app\models\FirmRubric;
//use app\models\Firm;
//use app\models\Building;
//use app\models\Rubric;
//use app\models\Phone;

class DataGeneratorController extends \yii\console\Controller
{
    /**
     * Clears database and creates test data
     *
     * @throws \Exception
     */
    public function actionGenerate()
    {
        $this->stdout("Warning! All previous data will be removed from DB. Do you want continue (y/n)?", Console::BG_RED);
        $answer = trim(fgets(STDIN));

        if ($answer != 'y') {
            exit;
        }

        $dataGenerator = new DataGenerator();

        $this->stdout("-- Delete all previous data! --\n", Console::FG_YELLOW);
        $dataGenerator->deleteAllData();
        $this->stdout("Done\n", Console::FG_GREEN);

        $this->stdout("-- Start data generating! --\n", Console::FG_YELLOW);

        $startTime = microtime(true);

        // buildings
        $this->stdout("Generate buildings...\n", Console::FG_YELLOW);
        $result = $dataGenerator->generateBuildings();
        $this->stdout("Generated $result buildings\n");
        $this->stdout("Done\n", Console::FG_GREEN);

        // rubrics
        $this->stdout("Generate rubrics...\n", Console::FG_YELLOW);
        $result = $dataGenerator->generateRubrics();
        $this->stdout("Generated $result rubrics\n");
        $this->stdout("Done\n", Console::FG_GREEN);

        // firms
        $this->stdout("Generate firms...\n", Console::FG_YELLOW);
        $result = $dataGenerator->generateFirms();
        $this->stdout("Generated {$result['insertedFirms']} firms, {$result['insertedPhones']} phones. ");
        $this->stdout("Linked with {$result['insertedFirmRubrics']} rubrics\n");
        $this->stdout("Done\n", Console::FG_GREEN);

        $stopTime = microtime(true);
        $diff = $stopTime - $startTime;
        $this->stdout("\nTotal script generation time = $diff\n");
    }
}