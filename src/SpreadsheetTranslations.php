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

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\SiteEvent;
use craft\services\Sites;
use craft\services\Utilities;
use craft\web\UrlManager;
use internetztube\spreadsheetTranslations\services\FetchService;
use internetztube\spreadsheetTranslations\services\MissingHandleService;
use internetztube\spreadsheetTranslations\services\MissingLanguagesService;
use internetztube\spreadsheetTranslations\services\TemplateTranslationService;
use internetztube\spreadsheetTranslations\services\WriteService;
use internetztube\spreadsheetTranslations\services\WriteTranslationsToDiskService;
use internetztube\spreadsheetTranslations\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use internetztube\spreadsheetTranslations\utilities\SpreadsheetTranslationsUtility;
use Twig\Loader\FilesystemLoader;
use yii\base\Event;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;


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

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'internetztube\spreadsheetTranslations\console\controllers';
        }

        $this->setComponents([
            'fetch' => FetchService::class,
            'missingLanguages' => MissingLanguagesService::class,
            'templateTranslation' => TemplateTranslationService::class,
            'writeTranslationsToDisk' => WriteTranslationsToDiskService::class,
            'missingHandle' => MissingHandleService::class,
        ]);

        // Register our utilities
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = SpreadsheetTranslationsUtility::class;
        });


        Event::on(Sites::class, Sites::EVENT_AFTER_SAVE_SITE, function(SiteEvent $siteEvent) {
            $this->missingLanguages->addMissingLanguages();
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge(
                $event->rules,
                include __DIR__ . '/cpRoutes.php',
            );
        });
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
