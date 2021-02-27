<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\models\Site;

class TranslationCategoryService extends BaseSpreadsheetService
{
    public function categories(): array
    {
        $categories = [];
        $folder = parent::translationPath();
        $languageFolders = scandir($folder);
        foreach ($languageFolders as $languageFolder) {
            $languageFolderPath = $folder . DIRECTORY_SEPARATOR . $languageFolder;
            if (in_array($languageFolder, ['..', '.']) || !is_dir($languageFolderPath)) continue;

            $files = scandir($languageFolderPath);
            foreach ($files as $fileName) {
                $filePath = $languageFolderPath . DIRECTORY_SEPARATOR . $fileName;
                if (!file_exists($filePath)) continue;
                $pathInfo = pathinfo($filePath);
                if ($pathInfo['extension'] !== 'php') continue;
                $categories[] = $pathInfo['filename'];
            }
        }

        return array_unique($categories);
    }

    public function handlesFromCategory(string $translationCategory)
    {
        $handles = [];
        $folder = parent::translationPath();
        $languageFolders = scandir($folder);
        foreach ($languageFolders as $languageFolder) {
            $languageFolderPath = $folder . DIRECTORY_SEPARATOR . $languageFolder;
            if (in_array($languageFolder, ['..', '.']) || !is_dir($languageFolderPath)) continue;

            $files = scandir($languageFolderPath);
            foreach ($files as $fileName) {
                $filePath = $languageFolderPath . DIRECTORY_SEPARATOR . $fileName;
                $pathInfo = pathinfo($filePath);
                if ($pathInfo['filename'] !== $translationCategory) continue;
                if (!file_exists($filePath)) continue;
                if ($pathInfo['extension'] !== 'php') continue;
                $fileContent = include $filePath;
                if (!is_array($fileContent)) continue;
                $handles = array_merge($handles, array_keys($fileContent));
            }
        }

        return array_unique($handles);
    }
}
