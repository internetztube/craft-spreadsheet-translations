<?php

namespace internetztube\spreadsheetTranslations;

use craft\base\Component;
use craft\helpers\ArrayHelper;
use internetztube\spreadsheetTranslations\exceptions\GoogleAccessTokenExcepiredException;

class GoogleSpreadsheetsService extends Component
{
    /** @var \Google_Client */
    private $spreadSheetService;

    private const TRANSLATE_CATEGORY = 'site';
    private const SPREADSHEET_TAB_PUSH_TITLE = '_new';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Starts the process.
     * @throws \Google_Exception
     */
    public function fetchNewTranslations()
    {
        $translations = $this->translationsFromSheet();
        $this->exportTranslations($translations);
        $handles = $this->getUniqueHandlesFromTranslations($translations);
        $languages = $this->getUniqueLanguagesFromTranslations($translations);
        return (object)[
            'languages' => $languages,
            'handles' => $handles,
        ];
    }

    public function translate($category, $message, $params, $language)
    {
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        $language = $language ?: \Craft::$app->language;
        $result = \Craft::t($category, $message, $params, $language);

        if (\Craft::$app->request->isCpRequest) return $result;
        if (\Craft::$app->request->isConsoleRequest) return $result;
        if ($category !== self::TRANSLATE_CATEGORY) return $result;
        if ($result !== $message) return $result;
        if (!$settings->isPushEnabled) return $result;

        $path = sprintf('%s/%s/%s.php', $this->translationPath(), $language, self::TRANSLATE_CATEGORY);
        if (file_exists($path)) {
            $translationFile = include $path;
            if (isset($translationFile[$message])) return $result;
        }
        $translationsWithExcluded = $this->translationsFromSheet(false);
        if ($this->isTranslationHandleInSpreadSheet($translationsWithExcluded, $message)) return $result;
        $this->pushHandleToSpreadSheet($message);
        return $result;
    }

    protected function isTranslationHandleInSpreadSheet(array $translations, string $handle)
    {
        foreach ($translations as $translation) {
            if ($translation->handle === $handle) return true;
        }
        return false;
    }

    protected function pushHandleToSpreadSheet(string $handle)
    {
        $spreadSheetService = $this->getSpreadSheetService();
        /** @var \Google_Service_Sheets_Sheet $sheet */
        $emptySheet = null;
        $range = "A1:B";
        $valueRange= new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues(["values" => [$handle]]);
        $conf = ["valueInputOption" => "RAW"];
        $ins = ["insertDataOption" => "INSERT_ROWS"];

        $range = sprintf('%s!%s:%s', self::SPREADSHEET_TAB_PUSH_TITLE, 'A2', 'A2');
        $spreadSheetService->spreadsheets_values->append($this->getSpreadSheetId(), $range, $valueRange, $conf, $ins);
    }

    /**
     * Fetches and squeezes the data into translation objects.
     * @example See extractValuesFromSheet
     * @return array
     * @throws \Google_Exception
     */
    protected function translationsFromSheet(bool $excludeUnderscoreTabs = true)
    {
        $spreadSheetService = $this->getSpreadSheetService();
        $sheets = $spreadSheetService->spreadsheets->get($this->getSpreadSheetId());
        $translations = [];

        $languages = [];
        $sheetsData = [];
        /** @var \Google_Service_Sheets_Sheet $sheet */
        foreach ($sheets->getSheets() as $sheet) {
            $title = $sheet->getProperties()->getTitle();
            $rowCount = $sheet->getProperties()->getGridProperties()->rowCount;
            $columnCount = $sheet->getProperties()->getGridProperties()->columnCount;
            if ($excludeUnderscoreTabs && $title[0] === '_') continue;

            $range = $this->buildRangeFromNumbers($title, 1, 1, $columnCount, $rowCount);
            $result = $spreadSheetService->spreadsheets_values->get($this->getSpreadSheetId(), $range);
            $rows = $result->getValues();
            if (!is_array($rows)) {
                $rows = [];
            }
            if (count($rows) <= 0) continue;

            $sheetLanguages = array_shift($rows);
            if ($excludeUnderscoreTabs & count($sheetLanguages) <= 1) continue;

            array_shift($sheetLanguages);
            $languages = array_unique(array_merge($sheetLanguages, $languages));

            $result = $this->extractValuesFromSheet($rows, $sheetLanguages, $languages);
            $translations = array_merge($translations, $result);
        }
        return $translations;
    }

    /**
     * Finally writes the translations to disk.
     * @param array $translations
     */
    protected function exportTranslations(array $translations)
    {
        $files = $this->prepareTranslationsForExport($translations);

        foreach ($files as $language => $fileContent) {
            $languageFolder = $this->translationPath() . '/' . $language;
            if (!file_exists($languageFolder)) mkdir($languageFolder);
            $filename = sprintf('%s/%s.php', $languageFolder, self::TRANSLATE_CATEGORY);
            file_put_contents($filename, $fileContent);
        }
    }

    /**
     * Directory where the translations are located.
     * @return string
     */
    protected function translationPath()
    {
        $basePath = \Craft::$app->config->getConfigFilePath('../yo');
        $basePath = realpath(dirname($basePath));
        return $basePath . '/translations';
    }

    /**
     * Returns all languages that occur in the given translations.
     * @example ["de_AT", "en", ...]
     * @param array $translations
     * @return array
     */
    protected function getUniqueLanguagesFromTranslations(array $translations)
    {
        $languages = array_map(function ($translation) {
            return $translation->language;
        }, $translations);
        return array_unique(array_values($languages));
    }


    /**
     * Returns all translation handles the occur in the given translations.
     * @example ["homepage.hero.headline", ...]
     * @param array $translations
     * @return array
     */
    protected function getUniqueHandlesFromTranslations(array $translations)
    {
        $handles = array_map(function ($translation) {
            return $translation->handle;
        }, $translations);
        return array_unique(array_values($handles));
    }

    /**
     * Converts a number to letters in the speadsheets format.
     * @example 1 -> "A", 26 -> "Z", 27 -> "AA", ...
     * @param int $number
     * @return string|null
     */
    protected function rangeNumberToLetters(int $number)
    {
        if ($number <= 0) return null;
        $temp;
        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }

    /**
     * Converts letters to a number.
     * @example "A" -> 1, "Z" -> 26, "AA" -> 27, ...
     * @param string $letters
     * @return float|int
     */
    protected function rangeLettersToNumber(string $letters)
    {
        $letters = strtoupper($letters);
        $letters = preg_replace("/[^A-Z]/", '', $letters);
        $column = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $column += (ord($letters[$i]) - 64) * pow(26, $length - $i - 1);
        }
        return $column;
    }

    /**
     * Build a range string for quering in a spreadsheet.
     * @example "title!A1:E4"
     * @param string $title
     * @param int $startColumn
     * @param int $startRow
     * @param int $endColum
     * @param int $endRow
     * @return string
     */
    protected function buildRangeFromNumbers(string $title, int $startColumn, int $startRow, int $endColum, int $endRow)
    {
        return vsprintf('%s!%s%d:%s%d', [
            $title,
            $this->rangeNumberToLetters($startColumn),
            $startRow,
            $this->rangeNumberToLetters($endColum),
            $endRow
        ]);
    }


    /**
     * Builds an assoc array of translation filed.
     * ["de_AT" => "<?php \n return [ ... ] ", ... ]
     * @param array $translations
     * @return array
     */
    protected function prepareTranslationsForExport(array $translations): array
    {
        $result = [];

        $languages = $this->getUniqueLanguagesFromTranslations($translations);
        $handles = $this->getUniqueHandlesFromTranslations($translations);

        // This is the base for all translations, so every translation file has every key, even it's empty.
        $baseHandles = array_flip($handles);
        $baseHandles = array_map(function ($handle) {
            return '';
        }, $baseHandles);

        foreach ($languages as $language) {
            $content = "<?php\n\nreturn [\n";

            // Set translations into the base translations object.
            $assocTranslations = $baseHandles;
            foreach ($translations as $translation) {
                if ($translation->language !== $language) continue;
                $assocTranslations[$translation->handle] = $translation->value;
            }
            foreach ($assocTranslations as $key => $value) {
                if (!$value) $value = $key;
                $content .= sprintf("    '%s' => '%s',\n",
                    addslashes($key),
                    addslashes($value)
                );
            }
            $content .= '];';
            $result[$language] = $content;
        }
       return $result;
    }

    /**
     * Returns a Instance of \Google_Service_Sheets with a prepared client.
     * @return \Google_Service_Sheets
     * @throws \Google_Exception
     */
    protected function getSpreadSheetService(): \Google_Service_Sheets
    {
        if ($this->spreadSheetService) return $this->spreadSheetService;
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        $keyFileContents = json_decode($settings->keyFileContents, true);
        $client = new \Google_Client();
        $client->setApplicationName(SpreadsheetTranslations::class);
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($keyFileContents);
        return new \Google_Service_Sheets($client);
    }

    /**
     * Get the corresponding Spread Sheet Id from the config.
     * @return mixed
     */
    protected function getSpreadSheetId(): string
    {
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        return $settings->spreadSheetId;;
    }

    /**
     * Extract translations from sheet rows.
     * @example
     * [
     *     [
     *         'language' => 'de_AT',
     *         'handle' => 'homepage.hero.headline,
     *          'value' => 'Look at my product!',
     *     ],
     *     ...
     * ]
     * @param array $sheetRows
     * @param array $sheetLanguages
     * @return array
     */
    protected function extractValuesFromSheet(array $sheetRows, array $sheetLanguages, array $languages)
    {
        $result = [];
        $addStuff = function($language, $handle, $value) {
            $key = strtolower($language . '--' . $handle);
            return [
                $key => (object)[
                    'language' => $language,
                    'handle' => $handle,
                    'value' => $value,
                ]
            ];
        };
        foreach ($sheetRows as $row) {
            if (count($row) <= 0) continue;
            $handle = $row[0];
            if (!$handle) continue;
            $value = '';
            foreach ($languages as $language) {
                $result = array_merge($result, $addStuff($language, $handle, $value));
            }
            for ($i = 1; $i < count($sheetLanguages) + 1; $i++) {
                if (isset($row[$i])) $value = $row[$i];
                $language = $sheetLanguages[$i - 1];
                $result = array_merge($result, $addStuff($language, $handle, $value));
            }
        }
        return $result;
    }
}