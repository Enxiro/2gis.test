<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\rest\Controller;
use yii\data\ActiveDataProvider;

use yii\web\NotFoundHttpException;

use api\modules\v1\models\Building;

class BuildingController extends Controller
{
    // TODO
    const CACHE_DURATION = 60;

    const DEFAULT_PAGE_SIZE = 20;
    const MIX_PAGE_SIZE = 1;
    const MAX_PAGE_SIZE = 50;

    /**
     * @api {get} /v1/buildings Request buildings list
     * @apiParam {Number} [page=1]
     * @apiParam {Number} [per-page=20] min 1, max 50
     *
     * @apiName GetBuildings
     * @apiGroup Building
     *
     * @apiSampleRequest /buildings
     *
     * @apiSuccess {Object[]} buildings       List of buildings.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *           {
     *               "id": "4001",
     *               "street": "Мартынов Street",
     *               "building_number": "55",
     *               "lat": "20.18014100",
     *               "lng": "-101.80668800"
     *           },
     *           {
     *
     *               "id": "4002",
     *               "street": "Мишин Street",
     *               "building_number": "15",
     *               "lat": "39.59943100",
     *               "lng": "107.25838300"
     *           }
     *     ]
     *
     * @return mixed|ActiveDataProvider
     */
    public function actionIndex()
    {
        // Add current query params to cache key
        $cacheKey = Yii::$app->request->queryParams;
        // Add unique cache name
        $cacheKey[] = 'buildings';

        $cache = Yii::$app->cache;

        $activeDataProvider = $cache->get($cacheKey);
        if (!$activeDataProvider) {
            $query = Building::find()->asArray();

            $activeDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSizeLimit' => [self::MIX_PAGE_SIZE, self::MAX_PAGE_SIZE],
                    'defaultPageSize' => self::DEFAULT_PAGE_SIZE
                ]
            ]);
            $activeDataProvider->prepare();

            $cache->set($cacheKey, $activeDataProvider, self::CACHE_DURATION);
        }

        return $activeDataProvider;
    }

    /**
     * @api {get} /v1/buildings/:id Request building
     * @apiParam {Number} id Users unique ID.
     *
     * @apiName GetBuilding
     * @apiGroup Building
     *
     * @apiSampleRequest /buildings/:id
     *
     * @apiSuccess Object[] building       Building object.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "id": "4001",
     *         "street": "Мартынов Street",
     *         "building_number": "55",
     *         "lat": "20.18014100",
     *         "lng": "-101.80668800"
     *     }
     *
     * @param $id
     * @return null|static
     */
    public function actionView($id)
    {
        $cacheKey = [
            'building',
            $id
        ];
        $cache = Yii::$app->cache;

        $building = $cache->get($cacheKey);
        if ($building === false) {
            $building = Building::findOne($id);

            $cache->set($cacheKey, $building, self::CACHE_DURATION);
        }

        if ($building === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $building;
    }
}
