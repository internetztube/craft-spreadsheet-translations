<?php
/**
 * Spreadsheet Translations plugin for Craft CMS 3.x
 *
 * Adds support for dynamic translations from Google Spreadsheets.
 *
 * @link      https://github.com/internetztube
 * @copyright Copyright (c) 2019 Frederic Köberl
 */

namespace internetztube\spreadsheetTranslations\twigextensions;

use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

use Craft;

/**
 * @author    Frederic Köberl
 * @package   SpreadsheetTranslations
 * @since     1.0.0
 */
class SpreadsheetTranslationsTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'SpreadsheetTranslations';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('t', [$this, 'translateFilter']),
        ];
    }

    public function translateFilter($message, $category = null, $params = null, $language = null): string
    {
        // The front end site doesn't need to specify the category
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if (is_array($category)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $language = $params;
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $params = $category;
            $category = 'site';
        } else if ($category === null) {
            $category = 'site';
        }

        if ($params === null) {
            $params = [];
        }

        try {
            return SpreadsheetTranslations::$plugin->googleSpreadsheets->translate($category, (string)$message, $params, $language);
        } catch (InvalidConfigException $e) {
            return $message;
        }
    }
}
