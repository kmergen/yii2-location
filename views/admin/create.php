<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model kmergen\location\models\Location */

$this->title = Yii::t('loc', 'Create Location');
$this->params['breadcrumbs'][] = ['label' => Yii::t('loc', 'Locations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="location-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
