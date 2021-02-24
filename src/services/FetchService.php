<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\models\Site;

class FetchService extends BaseSpreadsheetService
{
    public function checkCredentials(string $translationCategory): bool
    {
            $this->getGoogleSheetsService(true);
            $this->rawRows($translationCategory);
        try {
        } catch (\Exception $exception) {
            throw $exception;
            return false;
        }
        return true;
    }

    /**
     * Returns all languages which are also avalible in Craft.
     * @param array $rawRows
     * @return array
     */
    public function languages(array $rawRows): array
    {
        if (count($rawRows) <= 0) return [];
        $sheetLanguages = $rawRows[0];
        array_shift($sheetLanguages);

        $craftLanguages = array_map(function(Site $site) {
            return $site->language;
        }, \Craft::$app->sites->getAllSites());

        $craftLanguages = array_unique($craftLanguages);

        return array_filter($sheetLanguages, function(string $sheetLanguage) use($craftLanguages) {
            return in_array($sheetLanguage, $craftLanguages);
        });
    }

    public function translations(array $rawRows)
    {
        $langauges = $this->languages($rawRows);
        if (count($langauges) <= 0) return [];

        $sheetLanguages = array_shift($rawRows);
        $result = [];

        foreach ($rawRows as $rawRow) {
            $row = $this->fillArrayToLengthWithValue($rawRow, count($sheetLanguages), []);
            $handle = $row[0];
            if (empty($handle)) continue;
            for ($i = 1; $i < count($sheetLanguages); $i++) {
                if (!in_array($sheetLanguages[$i], $langauges)) continue;
                $key = sprintf('%s-%s', $handle, $sheetLanguages[$i]);
                $result[$key] = [
                    'handle' => $handle,
                    'language' => $sheetLanguages[$i],
                    'value' => empty($row[$i]) ? $handle : $row[$i],
                    'isEmpty' => empty($row[$i]),
                ];
            }
        }
        return array_values($result);
    }

    public function rawRows(string $translationCategory, bool $forceRebuild = false): array
    {
        $contentRange = $this->getContentRange($translationCategory);
        if (!$contentRange) return [];
        $range = $contentRange->stringRepresentation;
        if (!$range) return [];

        $result = $this->getGoogleSheetsService($forceRebuild)
            ->spreadsheets_values
            ->get($this->getSpreadSheetId(), $range)
            ->getValues();

        if (!is_array($result)) return [];
        return $result;
    }

    public function getSheetId(string $translationCategory)
    {
        $sheets = $this->getApiSheets();
        foreach ($sheets as $sheet) {
            if (strcmp($this->getSpreadSheetContentTabName($translationCategory), $sheet->properties->title) === 0) {
                return $sheet->properties->sheetId;
            }
        }
        return null;
    }
}
