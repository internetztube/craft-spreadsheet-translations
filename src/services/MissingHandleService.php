<?php

namespace internetztube\spreadsheetTranslations\services;

use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class MissingHandleService extends BaseSpreadsheetService
{
    public function pushHandleToSpreadSheet($handles)
    {
        $this->createTranslationsSheetWhenNotPresent();
        $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
        $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);

        // When there are no languages present, the translation mapping breaks. So sync the translations and try again.
        if (count($rawRows) >= 1 && count($translations) === 0) {
            SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
            return $this->pushHandleToSpreadSheet($handles);
        }
        $translationHandles = array_map(function($translation) {
            return $translation['handle'];
        }, $translations);

        $translationHandles = array_unique($translationHandles);

        if (!is_array($handles)) {
            $handles = [$handles];
        }

        $result = [];

        $preparedHandles = [];
        foreach ($handles as $handle) {
            if (in_array($handle, $translationHandles)) continue;
            $result[] = $handle;
            $preparedHandles[] = [$handle];
        }

        $spreadSheetService = $this->getGoogleSheetsService();
        /** @var \Google_Service_Sheets_Sheet $sheet */
        $emptySheet = null;
        $conf = ["valueInputOption" => "RAW"];
        $ins = ["insertDataOption" => "INSERT_ROWS"];

        $range = sprintf('%s!%s:%s', $this->getSpreadSheetContentTabName(), 'A2', 'A2');
        $valueRange = new \Google_Service_Sheets_ValueRange(["values" => $preparedHandles]);
        $spreadSheetService->spreadsheets_values->append($this->getSpreadSheetId(), $range, $valueRange, $conf, $ins);

        return $result;
    }
}
