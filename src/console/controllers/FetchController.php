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
        $result = SpreadsheetTranslations::$plugin->googleSpreadsheets->fetchNewTranslations();
        $this->stdout(sprintf("found %d languages\n", count($result->languages)));
        foreach ($result->languages as $language) {
            $this->stdout(sprintf("  %s\n", $language));
        }
        $this->stdout(sprintf("found %d translation handles\n", count($result->handles)));
        foreach ($result->handles as $handle) {
            $this->stdout(sprintf("  %s\n", $handle));
        }
    }
}
