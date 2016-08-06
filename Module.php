<?php

namespace kmergen\location;

/**
 * Admin module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'kmergen\location\controllers';
    public $defaultRoute = 'location/index';
  
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();

        // custom initialization code goes here
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['location*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => __DIR__ . '/messages',
        ];
    }

}
