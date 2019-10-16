<?php

namespace internetztube\spreadsheetTranslations\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class UtilityAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@internetztube/spreadsheetTranslations/resources/dist/";
        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/utility.js',
        ];

        $this->css = [
            'css/utility.css',
        ];

        parent::init();
    }
}
