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
    public $sidebarName = 'Spreadsheet Translations';

    public function rules()
    {
        return [
            ['keyFileContents', 'required'],
            ['spreadSheetId', 'required'],
            ['sheetContentTabName', 'required'],
            ['sidebarName', 'required'],
        ];
    }
}
