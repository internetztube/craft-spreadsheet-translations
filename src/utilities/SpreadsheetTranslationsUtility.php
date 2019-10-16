<?php

namespace internetztube\spreadsheetTranslations\utilities;

use internetztube\spreadsheetTranslations\assetbundles\UtilityAsset;
use Craft;
use craft\base\Utility;

class SpreadsheetTranslationsUtility extends Utility
{
    public static function displayName(): string
    {
        return 'Spreadsheet Translations';
    }

    public static function id(): string
    {
        return 'spreadsheet-translations';
    }

    public static function iconPath()
    {
        return Craft::getAlias("@internetztube/spreadsheetTranslations/resources/dist/img/utility-icon.svg");
    }

    public static function badgeCount(): int
    {
        return 0;
    }

    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(UtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'spreadsheet-translations/_utility',
            [
                'someVar' => $someVar
            ]
        );
    }
}
