<?php

namespace internetztube\spreadsheetTranslations\utilities;

use craft\base\Utility;

class SpreadsheetTranslationsUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Spreadsheet Translations';
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'spreadsheet-translations';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@app/icons/photo.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return $view->renderTemplate('_components/utilities/SpreadsheetTranslations');
    }
}
