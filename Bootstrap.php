<?php
/*
 * This file is part of the yii2-location project.
 *
 * (c) Yii2-location project <http://github.com/kmergen/yii2-location/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace kmergen\location;

use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Bootstrap class implement the translation for the module
 * widgets.
 *
 * @author Klaus Mergen <kmergenweb@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{

    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app->hasModule('location') && ($module = $app->getModule('location')) instanceof Module) {

            $app->get('i18n')->translations['location*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }

}
