<?php

namespace internetztube\spreadsheetTranslations\controllers;

use craft\web\Controller;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class MainController extends Controller
{
    public function actionIndex()
    {
        try {
            $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
            $translationCategory = $translationCategories[0];
            $spreadsheetId = SpreadsheetTranslations::$plugin->fetch->getSpreadSheetId();
            $credentialsValid = SpreadsheetTranslations::$plugin->fetch->checkCredentials($translationCategory);
            $sheetId = SpreadsheetTranslations::$plugin->fetch->getSheetId($translationCategory);
            $url = sprintf('https://docs.google.com/spreadsheets/d/%s/edit#gid=%s', $spreadsheetId, $sheetId);
        } catch (\Exception $exception) {
            $credentialsValid = false;
            $url = null;
        }

        $data = [
            'url' => $url,
            'credentialsValid' => $credentialsValid,
        ];

        return $this->renderTemplate('spreadsheet-translations/index', $data);
    }
}
