<?php

namespace kmergen\location\models;

use Yii;

/**
 * This is the model class for table "location".
 *
 * @property integer $id
 * @property string $street
 * @property string $postcode
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $latitude
 * @property string $longitude
 */
class Location extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'location';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['city', 'country'], 'required'],
            [['latitude', 'longitude'], 'number'],
            [['street', 'city', 'state', 'country'], 'string', 'max' => 255],
            [['postcode'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('location', 'ID'),
            'street' => Yii::t('location', 'Street'),
            'postcode' => Yii::t('location', 'Postcode'),
            'city' => Yii::t('location', 'City'),
            'state' => Yii::t('location', 'State'),
            'country' => Yii::t('location', 'Country'),
            'latitude' => Yii::t('location', 'Latitude'),
            'longitude' => Yii::t('location', 'Longitude'),
        ];
    }

    /**
     * @inheritdoc
     * @return LocationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LocationQuery(get_called_class());
    }
}
