<?php

namespace internetztube\spreadsheetTranslations\services;

class TemplateTranslationService extends BaseSpreadsheetService
{
    private const DELIMITERS = [
        [
            'delimiterStart' => '$this->extensions[\'craft\web\twig\Extension\']->translateFilter("',
            'delimiterEnd' => '"',
            'fileExtensions' => ['html', 'twig'],
            'extractionMode' => 'twig',
        ],
        [
            // $t('___'
            'delimiterStart' => '$t(\'',
            'fileExtensions' => ['vue', 'js'],
            'delimiterEnd' => '\'',
            'extractionMode' => 'plain',
        ],
        [
            // $t("___"
            'delimiterStart' => '$t("',
            'fileExtensions' => ['vue', 'js'],
            'delimiterEnd' => '"',
            'extractionMode' => 'plain',
        ],
    ];

    /**
     * Entry point for extracting all translations from all templates.
     * @return array
     */
    public function getTranslationsFromTemplates(): array
    {
        $originalTemplatesPath = \Craft::$app->view->getTemplatesPath();
        $templatesDirectories = $this->templateDirectories();

        $result = [];

        foreach ($templatesDirectories as $directoryPath) {
          \Craft::$app->view->setTemplatesPath($directoryPath);
          $templatePaths = $this->getTemplatePaths($directoryPath);

          foreach ($templatePaths as $templatePath) {
              $translations = $this->getTranslationsFromTemplateFile($templatePath);
              $result = array_merge($result, $translations);
          }
        }

        \Craft::$app->view->setTemplatesPath($originalTemplatesPath);

        $result = array_unique($result);
        natcasesort($result);

        return $result;
    }

    /**
     * @param string $translationPath
     * @return array
     */
    private function getTranslationsFromTemplateFile(string $translationPath): array
    {
        $result = [];
        $fileExtention = pathinfo($translationPath, PATHINFO_EXTENSION);

        foreach (self::DELIMITERS as $delimiter) {
            if (!in_array($fileExtention, $delimiter['fileExtensions'])) { continue; }
            if ($delimiter['extractionMode'] === 'twig') {
                $extractionResult = $this->getTranslationsFromTwigTemplateFile($translationPath, $delimiter['delimiterStart'], $delimiter['delimiterEnd']);
            } elseif ($delimiter['extractionMode'] === 'plain') {
                $extractionResult = $this->getTranslationsFromPlainTemplateFile($translationPath, $delimiter['delimiterStart'], $delimiter['delimiterEnd']);
            }
            $result = array_merge($result, $extractionResult);
        }
        
        return $result;
    }

    /**
     * @param string $translationPath
     * @param string $delimiterStart
     * @param string $delimiterEnd
     * @return array
     */
    private function getTranslationsFromTwigTemplateFile(string $translationPath, string $delimiterStart, string $delimiterEnd): array
    {
        try {
            $source = \Craft::$app->getView()->getTwig()->getLoader()->getSourceContext($translationPath);
            $document = \Craft::$app->getView()->getTwig()->compileSource($source);
            return $this->extractTranslationsFromDocument($document, $delimiterStart, $delimiterEnd);
        } catch (\Exception $e) {
            // @TODO add logging
            return [];
        }
    }

    /**
     * @param string $translationPath
     * @param string $delimiterStart
     * @param string $delimiterEnd
     * @return array
     */
    private function getTranslationsFromPlainTemplateFile(string $translationPath, string $delimiterStart, string $delimiterEnd): array
    {
        $templatesPath = \Craft::$app->view->getTemplatesPath();
        $document = file_get_contents($templatesPath . '/' . $translationPath);
        return $this->extractTranslationsFromDocument($document, $delimiterStart, $delimiterEnd);
    }

    /**
     * @param string $document
     * @param string $delimiterStart
     * @param string $delimiterEnd
     * @return array
     */
    private function extractTranslationsFromDocument(string $document, string $delimiterStart, string $delimiterEnd): array
    {
        $result = explode($delimiterStart, $document);
        array_shift($result);
        return array_map(function ($item) use ($delimiterEnd) {
            return explode($delimiterEnd, $item)[0];
        }, $result);
    }

    /**
     * Returns all template file paths relative to the `templates/` folder.
     * @return array
     */
    private function getTemplatePaths($templatesPath): array
    {
        $result = $this->scanDirectoryRecursive($templatesPath);
        $result = array_filter($result, function ($item) {
            $extension = pathInfo($item, PATHINFO_EXTENSION);
            return in_array($extension, $this->getAllSupportedTemplateExtensions());
        });
        $result = array_map(function($item) {
            $info = pathInfo($item);
            return $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
        }, $result);
        return array_unique($result);
    }

    /**
     * Thanks! https://stackoverflow.com/a/46697247/2421121
     * @param string $dir
     * @return array
     */
    private function scanDirectoryRecursive(string $dir): array
    {
        $result = [];
        foreach (scandir($dir) as $filename) {
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

    /**
     * @return array
     */
    private function getAllSupportedTemplateExtensions(): array
    {
        $result = [];
        foreach (self::DELIMITERS as $delimiter) {
            $result = array_merge($result, $delimiter['fileExtensions']);
        }
        return array_unique($result);
    }
}