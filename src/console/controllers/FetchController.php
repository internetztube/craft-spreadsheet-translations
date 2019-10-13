<?php

namespace internetztube\spreadsheetTranslations\console\controllers;

use internetztube\asdf\Asdf;

use Craft;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use yii\console\Controller;
use yii\helpers\Console;

class FetchController extends Controller
{
    public function actionIndex()
    {
        $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
        $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);
        SpreadsheetTranslations::$plugin->writeTranslationsToDisk->persist($translations);
        $this->stdout(sprintf("Saved %d translations to disk.\n", count($translations)));
    }
}
