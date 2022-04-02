<?php

namespace internetztube\spreadsheetTranslations\utilities;

use Craft;
use craft\base\Utility;
use internetztube\spreadsheetTranslations\assetbundles\UtilityAsset;

class SpreadsheetTranslationsUtility extends Utility
{
    public static function displayName(): string
    {
        return \Craft::t('spreadsheet-translations', 'Spreadsheet Translations');
    }

    public static function id(): string
    {
        return 'spreadsheet-translations';
    }

    public static function iconPath(): ?string
    {
        return Craft::getAlias("@internetztube/spreadsheetTranslations/icon-mask.svg");
    }

    public static function badgeCount(): int
    {
        return 0;
    }

    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(UtilityAsset::class);
        return Craft::$app->getView()->renderTemplate('spreadsheet-translations/_utility');
    }
}
