<?php

namespace internetztube\spreadsheetTranslations\controllers;

use craft\web\Controller;
use internetztube\spreadsheetTranslations\SpreadsheetTranslations;

class SettingsController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionTestConfig()
    {
        $this->requirePostRequest();
        $body = \Craft::$app->request->getRawBody();
        $keyFileContents = \Craft::$app->request->getBodyParam('keyFileContents', null);
        $spreadsheetId = \Craft::$app->request->getBodyParam('spreadsheetId', null);
        SpreadsheetTranslations::$plugin->fetch->setKeyFileContents($keyFileContents);
        SpreadsheetTranslations::$plugin->fetch->setSpreadsheetId($spreadsheetId);

        $data = [
            'success' => true,
            'message' => null,
            'exception_class' => null,
        ];
        try {
            SpreadsheetTranslations::$plugin->fetch->getGoogleSheetsService(true);
            $rawRows = SpreadsheetTranslations::$plugin->fetch->rawRows();
        } catch (\Exception $e) {
            $data['message'] = $e->getMessage();
            $data['exception_class'] = get_class($e);
            $data['success'] = false;
        }

        return $this->asJson($data);
    }
}