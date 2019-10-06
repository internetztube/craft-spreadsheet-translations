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
    public $isPushEnabled = false;

    public function rules()
    {
        return [
            ['keyFileContents', 'string'],
            ['spreadSheetId', 'string'],
            ['isPushEnabled', 'boolean'],
        ];
    }
}
