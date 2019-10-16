<?php

namespace internetztube\spreadsheetTranslations\controllers;

use craft\web\Controller;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class UtilitiesController extends Controller
{
    public function actionPushLanguages()
    {
        $this->requireAdmin();
        $missingLaguages = SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
        $data = [
            'success' => true,
            'data' => $missingLaguages,
        ];
        return $this->asJson($data);
    }

    public function actionPushHandles()
    {
        $handles = SpreadsheetTranslations::$plugin->templateTranslation->getTranslationsFromTemplates();
        $missingHandles = SpreadsheetTranslations::$plugin->missingHandle->pushHandleToSpreadSheet($handles);

        $data = [
            'success' => true,
            'data' => $missingHandles,
        ];
        return $this->asJson($data);
    }

    public function actionPullTranslations()
    {
        $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
        $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);
        SpreadsheetTranslations::$plugin->writeTranslationsToDisk->persist($translations);

        $data = [
            'success' => true,
        ];
        return $this->asJson($data);
    }
}