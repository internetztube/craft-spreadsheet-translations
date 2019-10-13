<?php

namespace internetztube\spreadsheetTranslations\console\controllers;

use internetztube\asdf\Asdf;

use Craft;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use yii\console\Controller;
use yii\helpers\Console;

class PushController extends Controller
{
    public function actionMissingLanguages()
    {
        $missingLanguages = SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
        $this->stdout(sprintf("added %d missing languages\n", count($missingLanguages)));
        foreach ($missingLanguages as $missingLanguage) {
            $this->stdout(sprintf("  %s\n", $missingLanguage));
        }
    }

    public function actionMissingHandles()
    {
        $translations = SpreadsheetTranslations::$plugin->templateTranslation->getTranslationsFromTemplates();
        $insertedHandles = SpreadsheetTranslations::$plugin->missingHandle->pushHandleToSpreadSheet($translations);
        $this->stdout(sprintf("added %d missing handles\n", count($insertedHandles)));
    }
}
