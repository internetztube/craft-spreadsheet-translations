<?php

namespace internetztube\spreadsheetTranslations\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SettingsAssetBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@internetztube/spreadsheetTranslations/resources/dist/";
        $this->depends = [
            CpAsset::class,
        ];
        $this->css = [
            'css/settings.css',
        ];
        $this->js = [
            'js/settings.js',
        ];
        parent::init();
    }
}