<?php

namespace internetztube\spreadsheetTranslations\services;

use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class MissingHandleService extends BaseSpreadsheetService
{
    public function pushHandleToSpreadSheet(string $translationCategory, array $handles)
    {
        $this->createTranslationsSheetWhenNotPresent($translationCategory);
        $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows($translationCategory);
        $translations = SpreadsheetTranslations::$plugin->fetch->translations($rawRows);

        // When there are no languages present, the translation mapping breaks. So sync the translations and try again.
        if (count($rawRows) < 1 && count($translations) === 0) {
            SpreadsheetTranslations::$plugin->missingLanguages->addMissingLanguages();
            return $this->pushHandleToSpreadSheet($translationCategory, $handles);
        }
        $translationHandles = array_map(function ($translation) {
            return $translation['handle'];
        }, $translations);

        $translationHandles = array_unique($translationHandles);

        if (!is_array($handles)) {
            $handles = [$handles];
        }

        $languages = $rawRows[0];
        array_shift($languages);

        $result = [];

        $preparedHandles = [];
        foreach ($handles as $handle) {
            if (in_array($handle, $translationHandles)) continue;
            $result[] = $handle;

            $preparedRow = [];
            $preparedRow[] = $handle;
            foreach ($languages as $language) {
                $filePath = $this->translationPath() . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $translationCategory . '.php';
                if (!file_exists($filePath)) {
                    $preparedRow[] = '';
                    continue;
                }
                $fileContent = include $filePath;
                if (!isset($fileContent[$handle])) {
                    $preparedRow[] = '';
                    continue;
                }
                $preparedRow[] = $fileContent[$handle];
            }
            $preparedHandles[] = $preparedRow;
        }

        $spreadSheetService = $this->getGoogleSheetsService();
        /** @var \Google_Service_Sheets_Sheet $sheet */
        $emptySheet = null;
        $conf = ["valueInputOption" => "RAW"];
        $ins = ["insertDataOption" => "INSERT_ROWS"];

        $range = sprintf('%s!A2:A%s', $this->getSpreadSheetContentTabName($translationCategory), count($languages)+1);
        $valueRange = new \Google_Service_Sheets_ValueRange(["values" => $preparedHandles]);
        $spreadSheetService->spreadsheets_values->append($this->getSpreadSheetId(), $range, $valueRange, $conf, $ins);

        return $result;
    }
}
