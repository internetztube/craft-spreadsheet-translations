<?php

namespace internetztube\spreadsheetTranslations\controllers;

use craft\web\Controller;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class MainController extends Controller
{
    public function actionIndex()
    {
        $spreadsheetId = SpreadsheetTranslations::$plugin->fetch->getSpreadSheetId();
        $credentialsValid = SpreadsheetTranslations::$plugin->fetch->checkCredentials();
        $sheetId = SpreadsheetTranslations::$plugin->fetch->getSheetId();
        $url = sprintf('https://docs.google.com/spreadsheets/d/%s/edit#gid=%s', $spreadsheetId, $sheetId);

        $data = [
            'url' => $url,
            'credentialsValid' => $credentialsValid,
        ];

        return $this->renderTemplate('spreadsheet-translations/index', $data);
    }
}