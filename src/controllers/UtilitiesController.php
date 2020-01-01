<?php

namespace internetztube\spreadsheetTranslations\controllers;

use craft\web\Controller;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class UtilitiesController extends Controller
{
    public function actionPushLanguages()
    {
        $this->requireAdmin();
        try {
            $missingLanguages = SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
            $data = [
                'success' => true,
                'data' => $missingLanguages,
            ];
        } catch (\Exception $exception) {
            $data = [
                'success' => false,
            ];
        }

        return $this->asJson($data);
    }

    public function actionPushHandles()
    {
        try {
            $handles = SpreadsheetTranslations::$plugin->templateTranslation->getTranslationsFromTemplates();
            $missingHandles = SpreadsheetTranslations::$plugin->missingHandle->pushHandleToSpreadSheet($handles);

            $data = [
                'success' => true,
                'data' => $missingHandles,
            ];
        } catch (\Exception $exception) {
            $data = [
                'success' => false,
            ];
        }

        return $this->asJson($data);
    }

    public function actionPullTranslations()
    {
        try {
            $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
            $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);
            SpreadsheetTranslations::$plugin->writeTranslationsToDisk->persist($translations);

            $data = [
                'success' => true,
            ];
        } catch (\Exception $exception) {
            $data = [
                'success' => false,
            ];
        }

        return $this->asJson($data);
    }
}