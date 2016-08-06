<?php

namespace kmergen\location\controllers;

use Yii;
use kmergen\location\models\LocationSearch;

class AdminController extends \yii\web\Controller
{
   /**
     * Lists all Location models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LocationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
        ]);
    }

}
