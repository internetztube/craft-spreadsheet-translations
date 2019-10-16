<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\models\Site;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use internetztube\spreadsheetTranslations\services\BaseSpreadsheetService;

class MissingLanguagesService extends BaseSpreadsheetService
{
    public function addMissingLanguages(): array
    {
        $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
        $missingLanguages = $this->missingLanguages($rawRows);
        if (count($missingLanguages) <= 0) return [];

        if (count($rawRows) <= 0) {
            $range = $this->buildRangeString($this->getSpreadSheetContentTabName(), 2, 1, 2, 1);
        } else {
            $languageRow = array_shift($rawRows);
            $startColumn = count($languageRow)+1;
            $startColumn = $startColumn <= 1 ? 2 : $startColumn;
            $range = $this->buildRangeString($this->getSpreadSheetContentTabName(), $startColumn, 1, count($missingLanguages)+count($languageRow), 1);
        }

        $spreadSheetService = $this->getGoogleSheetsService();
        /** @var \Google_Service_Sheets_Sheet $sheet */
        $emptySheet = null;
        $valueRange=  new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues(["values" => $missingLanguages]);
        $conf = ["valueInputOption" => "RAW"];
        $ins = ["insertDataOption" => "INSERT_ROWS"];
        $spreadSheetService->spreadsheets_values->append($this->getSpreadSheetId(), $range, $valueRange, $conf, $ins);
        return $missingLanguages;
    }

    private function missingLanguages(array $rawRows)
    {
        $missingLanguages = [];
        $langauges = SpreadsheetTranslations::$plugin->fetch->languages($rawRows);
        $contentRange = $this->getContentRange();
        if (!$contentRange) return [];
        $columnsCount = $contentRange->sheetColumns;

        $craftSitesLanguages = array_map(function(Site $site) {
            return $site->language;
        }, \Craft::$app->sites->getAllSites());
        $craftSitesLanguages = array_unique($craftSitesLanguages);

        foreach ($craftSitesLanguages as $craftSitesLanguage) {
            if (in_array($craftSitesLanguage, $langauges)) continue;
            $missingLanguages[] = $craftSitesLanguage;
        }
        return $missingLanguages;
    }
}