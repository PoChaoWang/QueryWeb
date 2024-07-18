<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Exception;

class GoogleService
{
    protected $client;
    protected $googleSheetService;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Google Sheets API PHP');
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->googleSheetService = new Google_Service_Sheets($this->client);
    }

    public function readGoogleSheet($spreadSheetId, $sheetName)
    {
        $dimensions = $this->getDimensions($spreadSheetId, $sheetName);
        $range = $sheetName . '!A1:' . $dimensions['colCount'];
        $data = $this->googleSheetService
            ->spreadsheets_values
            ->batchGet($spreadSheetId, ['ranges' => $range]);

        return $data->getValueRanges()[0]->values;
    }

    private function getDimensions($spreadSheetId, $sheetName)
    {
        $rowDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => $sheetName . '!A:A','majorDimension'=>'COLUMNS']
        );

        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (!$rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => $sheetName . '!1:1','majorDimension'=>'ROWS']
        );

        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (!$colMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        return [
            'error' => false,
            'rowCount' => count($rowMeta[0]),
            'colCount' => $this->colLengthToColumnAddress(count($colMeta[0]))
        ];
    }

    private function colLengthToColumnAddress($number)
    {
        if ($number <= 0) return null;

        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }

    public function getLastRow($spreadsheetId, $sheetName)
    {
        $range = $sheetName . '!A:A';
        $response = $this->googleSheetService->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        return count($values) + 1; // 返回最後一行的下一行
    }

    public function writeSheet($spreadsheetId, $range, $values, $append = false)
    {
        if ($append) {
            // 移除 header 行
            $values = array_slice($values, 1);
        }

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        try {
            if ($append) {
                $params['insertDataOption'] = 'INSERT_ROWS';
                $result = $this->googleSheetService->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            } else {
                $result = $this->googleSheetService->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception('Google Sheets API Error: ' . $e->getMessage());
        }

    }
}
