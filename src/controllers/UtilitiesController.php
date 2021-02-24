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
        $data = ['success' => true, 'data' => [],];

        try {
            $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
            foreach ($translationCategories as $translationCategory) {
                $translationHandles = SpreadsheetTranslations::$plugin->translationCategories->handlesFromCategory($translationCategory);
                if ($translationCategory === 'site') {
                    $templateHandles = SpreadsheetTranslations::$plugin->templateTranslation->getTranslationsFromTemplates();
                    $translationHandles = array_merge($templateHandles, $translationHandles);
                }

                $missingHandles = SpreadsheetTranslations::$plugin->missingHandle->pushHandleToSpreadSheet($translationCategory, $translationHandles);
                $data['data'] = array_merge($data['data'], $missingHandles);
                dd($data);
                continue;
            }
        } catch (\Exception $exception) {
            $data['success'] = false;
            $data['message'] = $exception->getMessage();
        }

        return $this->asJson($data);
    }

    public function actionPullTranslations()
    {
        try {
            $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
            foreach ($translationCategories as $translationCategory) {
                $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows($translationCategory);
                $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);
                SpreadsheetTranslations::$plugin->writeTranslationsToDisk->persist($translations, $translationCategory);
            }
            $data = [
              'success' => true,
            ];
        } catch (\Exception $exception) {
            $data = [
              'success' => false,
              'message' => $exception->getMessage(),
            ];
        }

        return $this->asJson($data);
    }
}
