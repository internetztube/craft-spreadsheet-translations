<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\base\Component;
use craft\helpers\FileHelper;
use Symfony\Component\Filesystem\Filesystem;

class TemplateTranslationService extends BaseSpreadsheetService
{
    private const SUPPORTED_TEMPLATE_EXTENSIONS = ['html', 'twig'];
    private const DELIMITER_START = '$this->extensions[\'craft\web\twig\Extension\']->translateFilter("';
    private const DELIMITER_END = '"';

    public function getTranslationsFromTemplates()
    {
        $originalTemplatesPath = \Craft::$app->view->getTemplatesPath();
        $templatesPath = $this->templatesPath();
        \Craft::$app->view->setTemplatesPath($templatesPath);
        $templatePaths = $this->getTemplatePaths();
        $result = [];
        foreach ($templatePaths as $templatePath) {
            $translations = $this->getTranslationsFromTemplateFile($templatePath);
            $result = array_merge($result, $translations);
        }
        \Craft::$app->view->setTemplatesPath($originalTemplatesPath);

        $result = array_unique($result);
        natcasesort($result);
        return $result;
    }

    private function getTranslationsFromTemplateFile(string $translationPath)
    {
        try {
            $source = \Craft::$app->getView()->getTwig()->getLoader()->getSourceContext($translationPath);
            $document = \Craft::$app->getView()->getTwig()->compileSource($source);
            return $this->extractTranslationsFromDocument($document);
        } catch (\Exception $e) {
            // @TODO add logging
            return [];
        }
    }

    private function extractTranslationsFromDocument(string $document)
    {
        $result = explode(self::DELIMITER_START, $document);
        array_shift($result);
        return array_map(function($item) {
            return explode(self::DELIMITER_END, $item)[0];
        }, $result);
    }

    private function getTemplatePaths()
    {
        $templatesPath = $this->templatesPath();
        $result = $this->scanDirectoryRecursive($templatesPath);
        $result = array_filter($result, function($item) {
            $extension = pathInfo($item, PATHINFO_EXTENSION);
            return in_array($extension, self::SUPPORTED_TEMPLATE_EXTENSIONS);
        });
        return array_map(function($item) use ($templatesPath) {
            $info = pathInfo($item);
            return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
        }, $result);
    }

    // Thanks! https://stackoverflow.com/a/46697247/2421121
    private function scanDirectoryRecursive(string $dir)
    {
        $result = [];
        foreach(scandir($dir) as $filename) {
            if ($filename[0] === '.') continue;
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) {
                foreach ($this->scanDirectoryRecursive($filePath) as $childFilename) {
                    $result[] = $filename . '/' . $childFilename;
                }
            } else {
                $result[] = $filename;
            }
        }
        return $result;
    }
}