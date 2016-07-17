<?php

namespace api\modules\v1\controllers;

use Yii;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;

use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;

use api\modules\v1\models\Firm;

class FirmController extends Controller
{
    // TODO
    const CACHE_DURATION = 1;

    const DEFAULT_PAGE_SIZE = 20;
    const MIX_PAGE_SIZE = 1;
    const MAX_PAGE_SIZE = 50;

    const POINT_TYPE_CIRCLE = 1;
    const POINT_TYPE_RECTANGLE = 2;

    /**
     * @api {get} /v1/firms Request buildings list
     * @apiParam {Number} [page=1]
     * @apiParam {Number} [per-page=20] min 1, max 50
     * @apiParam {Number} [building-id] Get firms of building
     * @apiParam {Number} [rubric-id] Get firms of rubric
     *
     * @apiParam {Number} [point-lat] Required if uses search by area. Point latitude value. Uses for search firms by area
     * @apiParam {Number} [point-lng] Required if uses search by area. Point latitude value. Uses for search firms by area
     * @apiParam {Number} [point-type] Required if uses search by area. 1 - circle, 2 - rectangle
     * @apiParam {Number} [point-radius] Required if uses search by circle area. Value - degree
     * @apiParam {Number} [point-width] Required if uses search by rectangle area. Value - degree (full width of rectangle. point will be in the middle)
     * @apiParam {Number} [point-height] Required if uses search by rectangle area. Value - degree (full height of rectangle. point will be in the middle)
     *
     * @apiName GetFirms
     * @apiGroup Firm
     *
     * @apiSampleRequest /firms
     *
     * @apiSuccess {Object[]} firms       List of firms.
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *          {
     *               "id": 1,
     *               "name": "ООО Компания ГаражАлмаз",
     *               "building_id": 112,
     *               "address": "Сергеев Street, 80",
     *               "address_point": {
     *                   "lat": "34.29617700",
     *                   "lng": "24.34471600"
     *               },
     *               "phones": [
     *                   {
     *                       "id": 1,
     *                       "firm_id": 1,
     *                       "type": 20,
     *                       "phone": "3200498"
     *                   },
     *                   {
     *                       "id": 2,
     *                       "firm_id": 1,
     *                       "type": 10,
     *                       "phone": "89352535095"
     *                   },
     *                   {
     *                       "id": 3,
     *                       "firm_id": 1,
     *                       "type": 20,
     *                       "phone": "3103771"
     *                   }
     *               ],
     *               "rubrics": [
     *                   {
     *                       "id": 52,
     *                       "parent_id": 49,
     *                       "name": "Перетягивание удава"
     *                   }
     *               ]
     *           }
     *       ]
     *
     * @return mixed|ActiveDataProvider
     */
    public function actionIndex()
    {
        // Add current query params to cache key
        $cacheKey = Yii::$app->request->queryParams;
        // Add unique cache name
        $cacheKey[] = 'firms';

        $cache = Yii::$app->cache;

        $activeDataProvider = $cache->get($cacheKey);
        if (!$activeDataProvider) {


            $query = Firm::find()
                ->select([
                    'firm.*',
//                    '( 3956 * 2 * ASIN(SQRT( POWER(SIN((:point_lat - abs( building.lat)) * pi()/180 / 2),2) +
//                    COS(:point_lat * pi()/180 ) * COS( abs(building.lat) *  pi()/180) * POWER(SIN( ( :point_lng – building.lng) *  pi()/180 / 2),
//                    2) )) ) as distance'

                    '( 3959 * acos( cos( radians(37) ) * cos( radians( building.lat ) ) * cos( radians( building.lng ) - radians(-122) ) + sin( radians(37) ) * sin( radians( building.lat ) ) ) ) AS distance'
                ])
                ->joinWith(['phones', 'building', 'firmRubrics'])
                ->addParams([
                    ':point_lat' => '5',
                    ':point_lng' => 5
                ]);

            // Get params
            $params = Yii::$app->request->queryParams;

            // Filter by building
            if (!empty($params['building-id'])) {
                $query->andFilterWhere([
                    'building_id' => $params['building-id']
                ]);
            }

            // Filter by rubric
            if (!empty($params['rubric-id'])) {
                $query->andFilterWhere([
                    'rubric_id' => $params['rubric-id']
                ]);
            }

            // Filter by radius
            if (
                !empty($params['point-lat']) ||
                !empty($params['point-lng']) ||
                !empty($params['point-type']) ||
                !empty($params['point-radius']) ||
                !empty($params['point-height']) ||
                !empty($params['point-width'])
            ) {
                if (empty($params['point-lat'])) {
                    throw new BadRequestHttpException ("Required param for search by point is missing: point-lat");
                }
                if (empty($params['point-lng'])) {
                    throw new BadRequestHttpException ("Required param for search by point is missing: point-lng");
                }
                if (empty($params['point-type'])) {
                    throw new BadRequestHttpException ("Required param for search by point is missing: point-type");
                }
                if ($params['point-type'] == self::POINT_TYPE_CIRCLE) {
                    if (empty($params['point-radius'])) {
                        throw new BadRequestHttpException ("Required param for search by circle point is missing: point-radius");
                    }
                    // TODO
                    // ...
                } elseif ($params['point-type'] == self::POINT_TYPE_RECTANGLE) {
                    if (empty($params['point-height'])) {
                        throw new BadRequestHttpException ("Required param for search by rectangle point is missing: point-height");
                    }
                    if (empty($params['point-width'])) {
                        throw new BadRequestHttpException ("Required param for search by rectangle point is missing: point-width");
                    }

                    $query->andFilterWhere([
                        '>=', 'building.lat', $params['point-lat'] - $params['point-width'] / 2
                    ])->andFilterWhere([
                        '<=', 'building.lat', $params['point-lat'] + $params['point-width'] / 2
                    ]);

                    $query->andFilterWhere([
                        '>=', 'building.lng', $params['point-lng'] - $params['point-height'] / 2
                    ])->andFilterWhere([
                        '<=', 'building.lng', $params['point-lng'] + $params['point-height'] / 2
                    ]);
                } else {
                    throw new BadRequestHttpException ("Bad value: point-type");
                }
            }

            // TODO phone numbers format
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
}
