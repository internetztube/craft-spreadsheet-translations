<?php

namespace internetztube\spreadsheetTranslations\services;



use craft\base\Component;
use craft\models\Site;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use internetztube\spreadsheetTranslations\services\BaseSpreadsheetService;
use internetztube\spreadsheetTranslations\utilities\GoogleSpreadsheetUtility;
use internetztube\spreadsheetTranslations\utilities\SpreadsheetUtility;

class FetchService extends BaseSpreadsheetService
{
    /**
     * Returns all languages which are also avalible in Craft.
     * @param array $rawRows
     * @return array
     */
    public function languages(array $rawRows): array
    {
        if (count($rawRows) <= 0) return [];
        $sheetLanguages = $rawRows[0];
        // the first cell of the sheet (A1:A1) MUST be empty!
        if (array_shift($sheetLanguages) !== '') return [];

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

    public function rawRows(): array
    {
        $contentRange = $this->getContentRange();
        if (!$contentRange) return [];
        $range = $contentRange->stringRepresentation;
        if (!$range) return [];

        $result = $this->getGoogleSheetsService()
            ->spreadsheets_values
            ->get($this->getSpreadSheetId(), $range)
            ->getValues();
        if (!$result) return [];
        return $result;
    }
}