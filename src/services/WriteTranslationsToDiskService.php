<?php

namespace internetztube\spreadsheetTranslations\services;

use craft\base\Component;

class WriteTranslationsToDiskService extends BaseSpreadsheetService
{
    public function persist(array $translations)
    {
        $files = [];
        $groupedTranslations = $this->groupTranslationsByHandleAndLanguage($translations);
        foreach ($groupedTranslations as $language => $groupByHandle) {
            $files[$language] = $this->buildFile($groupByHandle);
        }

        $translationPath = $this->translationPath();
        foreach ($files as $language => $value) {
            $directoryPath = $translationPath . DIRECTORY_SEPARATOR . $language;
            if (!file_exists($directoryPath)) mkdir($directoryPath);
            $filePath = $directoryPath . DIRECTORY_SEPARATOR . $this->getTranslationCategory() . '.php';
            file_put_contents($filePath, $value);
        }
    }

    private function buildFile(array $groupedTranslations)
    {
        $result = "<?php\n\nreturn [\n";
        foreach ($groupedTranslations as $handle => $translation) {
            $result .= sprintf("    \"%s\" => \"%s\", %s\n",
                addslashes($handle),
                addslashes($translation['value']),
                ($translation['isEmpty'] ? '// value is empty' : '')
            );
        }
        $result .= "];\n";
        return $result;
    }

    private function groupTranslationsByHandleAndLanguage(array $translations)
    {
        $result = [];
        foreach ($translations as $translation) {
            if (!isset($result[$translation['language']])) {
                $result[$translation['language']] = [];
            }
            $result[$translation['language']][$translation['handle']] = $translation;
        }
        return $result;
    }
}