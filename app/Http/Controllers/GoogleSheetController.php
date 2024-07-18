<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
class GoogleSheetController extends Controller
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

        // 獲取新的訪問令牌
        // $authUrl = $this->client->createAuthUrl();
        // printf("Open the following link in your browser:\n%s\n", $authUrl);
        // print 'Enter verification code: ';
        // $authCode = trim(fgets(STDIN));
        // $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        // $this->client->setAccessToken($accessToken);


        $this->googleSheetService = new Google_Service_Sheets($this->client);
    }

    public function readGoogleSheet()
    {
        $spreadSheetId= '1Jj8_0vUNlQdUSlZpIzHxfujKG-EBXw57pEf4h-o6gdw';
        $dimensions = $this->getDimensions($spreadSheetId);
        $range = 'testRead!A1:' . $dimensions['colCount'];
        $data = $this->googleSheetService
            ->spreadsheets_values
            ->batchGet($spreadSheetId, ['ranges' => $range]);

        return $data->getValueRanges()[0]->values;
    }

    // public function writeSheet($spreadsheetId, $range, $values, $append = false)
    // {
    //     $spreadsheetId = '<YOUR_SPREADSHEET_ID>';
    //     $range = '<RANGE_TO_WRITE>';

    //     $data = [['Column 1', 'Column 2', 'Column 3'],
    //             ['Data 1.1', 'Data 1.2', 'Data 1.3'],
    //             ['Data 2.1', 'Data 2.2', 'Data 2.3']];

    //     $this->client->setScopes([Sheets::SPREADSHEETS]);
    //     $service = new Sheets($this->client);

    //     $body = new Google_Service_Sheets_ValueRange([
    //         'values' => $data
    //     ]);

    //     $response = $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);

    //     // 处理写入响应
    // }

    private function getDimensions($spreadSheetId)
    {
        $rowDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'testRead!A:A','majorDimension'=>'COLUMNS']
        );

        //if data is present at nth row, it will return array till nth row
        //if all column values are empty, it returns null
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (! $rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'testRead!1:1','majorDimension'=>'ROWS']
        );

        //if data is present at nth col, it will return array till nth col
        //if all column values are empty, it returns null
        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (! $colMeta) {
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
}
