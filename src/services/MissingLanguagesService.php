<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\models\Site;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class MissingLanguagesService extends BaseSpreadsheetService
{
  /**
   * Pushes all missing languages from a category to the spreadsheet.
   * @param string $translationCategory
   * @return array
   */
    public function addMissingLanguages(): array
    {
        $result = [];
        $translationCategories = SpreadsheetTranslations::$plugin->translationCategories->categories();
        foreach ($translationCategories as $translationCategory) {
            $this->createTranslationsSheetWhenNotPresent($translationCategory);
            $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows($translationCategory);
            $missingLanguages = $this->missingLanguages($rawRows, $translationCategory);
            if (count($missingLanguages) <= 0) continue;
            $result = array_merge($result, $missingLanguages);

            if (count($rawRows) <= 0) {
                $range = $this->buildRangeString($this->getSpreadSheetContentTabName($translationCategory), 2, 1, 2, 1);
            } else {
                $languageRow = array_shift($rawRows);
                $startColumn = count($languageRow)+1;
                $startColumn = $startColumn <= 1 ? 2 : $startColumn;
                $range = $this->buildRangeString($this->getSpreadSheetContentTabName($translationCategory), $startColumn, 1, count($missingLanguages)+count($languageRow)+1, 1);
            }
            $spreadSheetService = $this->getGoogleSheetsService();
            $valueRange = new \Google_Service_Sheets_ValueRange();
            $valueRange->setValues(["values" => $missingLanguages]);
            $conf = ["valueInputOption" => "RAW"];
            $ins = ["insertDataOption" => "INSERT_ROWS"];
            $spreadSheetService->spreadsheets_values->append($this->getSpreadSheetId(), $range, $valueRange, $conf, $ins);
        }
        return array_unique($result);
    }

    /**
     * Returns all languages that are not already in the spreadsheet.
     * @param array $rawRows
     * @param string $translationCategory
     * @return array
     * @throws \Google_Exception
     */
    private function missingLanguages(array $rawRows, string $translationCategory)
    {
        $missingLanguages = [];
        $languages = SpreadsheetTranslations::$plugin->fetch->languages($rawRows);
        $contentRange = $this->getContentRange($translationCategory);
        if (!$contentRange) return [];

        $craftSitesLanguages = array_map(function(Site $site) {
            return $site->language;
        }, \Craft::$app->sites->getAllSites());
        $craftSitesLanguages = array_unique($craftSitesLanguages);

        foreach ($craftSitesLanguages as $craftSitesLanguage) {
            if (in_array($craftSitesLanguage, $languages)) continue;
            $missingLanguages[] = $craftSitesLanguage;
        }
        return $missingLanguages;
    }
}
