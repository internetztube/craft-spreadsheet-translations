<?php

namespace internetztube\spreadsheetTranslations\console\controllers;

use internetztube\asdf\Asdf;

use Craft;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Spreadsheet Translations
 */
class MainController extends Controller
{
    /**
     * Pulls all translations from the spreadsheet into static files on the disk. No handles get deleted!
     */
    public function actionPullTranslations()
    {
        $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
        foreach ($translationCategories as $translationCategory) {
            $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows($translationCategory);
            $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);
            SpreadsheetTranslations::$plugin->writeTranslationsToDisk->persist($translations, $translationCategory);
            $this->stdout(sprintf("%s: Saved %d translations to disk.\n", $translationCategory, count($translations)));
        }
    }

    /**
     * Pushes all missing site languages into the spreadsheet. No languages get deleted!
     */
    public function actionPushMissingLanguages()
    {
        $missingLanguages = SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
        $this->stdout(sprintf("added %d missing languages\n", count($missingLanguages)));
        foreach ($missingLanguages as $missingLanguage) {
            $this->stdout(sprintf("  %s\n", $missingLanguage));
        }
    }

    /**
     * Pushes all missing translation handles from the templates into the spreadsheet. No handles get deleted!
     */
    public function actionPushMissingHandles()
    {
        $missingHandles = [];

        $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
        foreach ($translationCategories as $translationCategory) {
            $translationHandles = SpreadsheetTranslations::$plugin->translationCategories->handlesFromCategory($translationCategory);
            if ($translationCategory === 'site') {
                $templateHandles = SpreadsheetTranslations::$plugin->templateTranslation->getTranslationsFromTemplates();
                $translationHandles = array_merge($templateHandles, $translationHandles);
            }
            $tmp = SpreadsheetTranslations::$plugin->missingHandle->pushHandleToSpreadSheet($translationCategory, $translationHandles);
            $missingHandles = array_merge($missingHandles, $tmp);
        }
        $this->stdout(sprintf("added %d missing handles\n", count($missingHandles)));
    }
}
