<?php
/**
 * Spreadsheet Translations plugin for Craft CMS 3.x
 *
 * Adds support for dynamic translations from Google Spreadsheets.
 *
 * @link      https://github.com/internetztube
 * @copyright Copyright (c) 2019 Frederic Köberl
 */

namespace internetztube\spreadsheetTranslations;

use internetztube\spreadsheetTranslations\twig;
use internetztube\spreadsheetTranslations\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use internetztube\spreadsheetTranslations\twigextensions\SpreadsheetTranslationsTwigExtension;
use yii\base\Event;

/**
 * Class SpreadsheetTranslations
 *
 * @author    Frederic Köberl
 * @package   SpreadsheetTranslations
 * @since     1.0.0
 *
 */
class SpreadsheetTranslations extends Plugin
{
    /** @var SpreadsheetTranslations */
    public static $plugin;
    public $schemaVersion = '1.0.0';
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new SpreadsheetTranslationsTwigExtension());

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'internetztube\spreadsheetTranslations\console\controllers';
        }

        $this->setComponents([
            'googleSpreadsheets' => GoogleSpreadsheetsService::class,
        ]);

        self::$plugin->googleSpreadsheets->init();
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'spreadsheet-translations/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
