<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\base\Component;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;

abstract class BaseSpreadsheetService extends Component
{
    /** @var string|null */
    private $spreadsheetId = null;

    /** @var string|null */
    private $keyFileContents = null;

    /** @var \Google_Service_Sheets | null */
    private $googleSheetsService;

    /** @var string */
    private $sheetContentTabName;

    /** @var array */
    private $apiSheets;

    /** @var string */
    private const TRANSLATE_CATEGORY = 'site';


    /**
     * Returns the name of the tab in which the translations are maintained.
     * @return string
     */
    public function getSpreadSheetContentTabName()
    {
        if ($this->sheetContentTabName) return $this->sheetContentTabName;
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        return $settings->sheetContentTabName;
    }

    /**
     * Returns the translation category in which the translation is in.
     * @return string
     */
    public function getTranslationCategory()
    {
        return self::TRANSLATE_CATEGORY;
    }

    /**
     * Directory where the translations are located.
     * @return string
     */
    public function translationPath(): string
    {
        $basePath = \Craft::$app->config->getConfigFilePath('../yo');
        $basePath = realpath(dirname($basePath));
        return $basePath . '/translations';
    }

    /**
     * Directory where the templates are located.
     * @return string
     */
    public function templatesPath(): string
    {
        $basePath = \Craft::$app->config->getConfigFilePath('../yo');
        $basePath = realpath(dirname($basePath));
        return $basePath . '/templates/';
    }

    /**
     * Converts a number to letters in the speadsheets format.
     * @example 1 -> "A", 26 -> "Z", 27 -> "AA", ...
     * @param int $number
     * @return string|null
     */
    public function rangeNumberToLetters(int $number): string
    {
        if ($number <= 0) return null;
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
     * @return int
     */
    public function rangeLettersToNumber(string $letters): int
    {
        $letters = strtoupper($letters);
        $letters = preg_replace("/[^A-Z]/", '', $letters);
        $column = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $column += (ord($letters[$i]) - 64) * pow(26, $length - $i - 1);
        }
        return (int) $column;
    }

    /**
     * Returns a Instance of \Google_Service_Sheets with a prepared client.
     * @param bool $forceRebuild
     * @return \Google_Service_Sheets
     * @throws \Google_Exception
     */
    public function getGoogleSheetsService(bool $forceRebuild = false): \Google_Service_Sheets
    {
        if ($this->googleSheetsService && !$forceRebuild) return $this->googleSheetsService;

        $keyFileContents = json_decode($this->getKeyFileContents(), true);
        $client = new \Google_Client();
        $client->setApplicationName(SpreadsheetTranslations::class);
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAccessType('offline');
        $client->setAuthConfig($keyFileContents);

        $googleSheetsService = $this->googleSheetsService = new \Google_Service_Sheets($client);
        return $googleSheetsService;
    }

    /**
     * Returns the cell range of the translations sheets.
     * @example "Translations!A1:C32"
     * @return string|null
     * @throws \Google_Exception
     */
    public function getContentRange(): ?object
    {
        $this->createTranslationsSheetWhenNotPresent();
        $sheets = $this->getApiSheets();

        /** @var \Google_Service_Sheets_Sheet $sheet */
        foreach ($sheets as $sheet) {
            $title = $sheet->getProperties()->getTitle();
            if ($title !== $this->getSpreadSheetContentTabName()) continue;
            $rowCount = $sheet->getProperties()->getGridProperties()->rowCount;
            $columnCount = $sheet->getProperties()->getGridProperties()->columnCount;

            $stringRepresentation = $this->buildRangeString($title, 1, 1, $columnCount, $rowCount);
            return (object) [
                'stringRepresentation' => $stringRepresentation,
                'sheetRows' => $rowCount,
                'sheetColumns' => $columnCount,
                'sheetTitle' => $title,
            ];
        }
        return null;
    }

    public function buildRangeString(string $title, int $startColumn, int $startRow, int $endColumn, int $endRow): string
    {
        return vsprintf('%s!%s%d:%s%s', [
            $title,
            $this->rangeNumberToLetters($startColumn),
            $startRow,
            $this->rangeNumberToLetters($endColumn),
            $endRow
        ]);
    }

    /**
     * Fills a array to a specified length with a specified value.
     * @example $input: [1,2,3,4], $length: 6, $value: -1 => [1,2,3,4,-1,-1]
     * @example $input: [1,2,3,4], $length: 2, $value: -1 => [1,2,3,4]
     * @param array $input
     * @param int $length
     * @param $value
     * @return array
     */
    public function fillArrayToLengthWithValue(array $input, int $length, $value): array
    {
        $inputLength = count($input);
        $diff = $length - $inputLength;
        if ($diff <= 0) return $input;
        $fillArray = array_fill(0, $diff, $value);
        $result = array_merge($input, $fillArray);
        return $result;
    }

    /**
     * Get the corresponding Spread Sheet Id from the config.
     * @return mixed
     */
    public function getSpreadSheetId(): string
    {
        if ($this->spreadsheetId) return $this->spreadsheetId;
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        return $settings->spreadSheetId;
    }

    /**
     * Here you can overwrite the spreadsheetId from the settings on the instance.
     * @param null $spreadsheetId
     */
    public function setSpreadsheetId($spreadsheetId = null)
    {
        $this->spreadsheetId = $spreadsheetId;
    }

    /**
     * Here you can overwrite the keyFileContents from the settings on the instance.
     * @param $keyFileContents
     */
    public function setKeyFileContents($keyFileContents)
    {
        $this->keyFileContents = $keyFileContents;
    }

    /**
     * Raw sheets from the corresponding spreadsheet.
     * @return array|\Google_Service_Sheets_Spreadsheet
     * @throws \Google_Exception
     */
    protected function getApiSheets()
    {
        if ($this->apiSheets) return $this->apiSheets;
        $this->apiSheets = $this->getGoogleSheetsService()
            ->spreadsheets
            ->get($this->getSpreadSheetId());
        return $this->apiSheets;
    }

    /**
     * Makes sure that the "Translations" sheet exists.
     * @throws \Google_Exception
     */
    protected function createTranslationsSheetWhenNotPresent()
    {
        $sheets = $this->getApiSheets();
        $doesTranslationSheetExist = false;
        foreach ($sheets as $sheet) {
            $title = $sheet->getProperties()->getTitle();
            if ($title === $this->getSpreadSheetContentTabName()) {
                $doesTranslationSheetExist = true;
                break;
            }
        }

        if ($doesTranslationSheetExist) return;

        $body = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                'addSheet' => [
                    'properties' => [
                        'title' => $this->getSpreadSheetContentTabName(),
                    ]
                ]
            ]
        ]);

         $this->getGoogleSheetsService()
            ->spreadsheets
            ->batchUpdate($this->getSpreadSheetId(), $body);
    }

    /**
     * Returns keyFileContents.
     * @return mixed|string|null
     */
    private function getKeyFileContents()
    {
        if ($this->keyFileContents) return $this->keyFileContents;
        $settings = SpreadsheetTranslations::$plugin->getSettings();
        return $settings->keyFileContents;
    }
}