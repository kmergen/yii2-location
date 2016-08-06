<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model kmergen\location\models\Location */

$this->title = Yii::t('loc', 'Update {modelClass}: ', [
    'modelClass' => 'Location',
]) . $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('loc', 'Locations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('loc', 'Update');
?>
<div class="location-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
