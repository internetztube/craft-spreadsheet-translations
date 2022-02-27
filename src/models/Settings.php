<?php

namespace internetztube\spreadsheetTranslations\models;

use internetztube\spreadsheetTranslations\SpreadsheetTranslations;
use paulzi\jsonBehavior\JsonBehavior;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $keyFileContents = '';
    public $spreadSheetId = '';
    public $sheetContentTabName = '';
    public $slugifyContentTabName = true;
    public $sidebarName = 'Spreadsheet Translations';
    public $templateDirectories = [[ 'directoryPath' => 'templates' ]];

    public function rules()
    {
        return [
            ['keyFileContents', 'required'],
            ['spreadSheetId', 'required'],
            ['sheetContentTabName', 'required'],
            ['sidebarName', 'required'],
            ['templateDirectories', 'required'],
        ];
    }
}
