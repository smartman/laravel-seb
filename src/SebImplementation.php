<?php

namespace Smartman\Seb;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SebImplementation
{

    protected $gatewayUrl;
    protected $privateKey;
    protected $keyPass;
    protected $certificate;
    protected $orgId;

    public function __construct()
    {
        $this->gatewayUrl  = config('seb.baltic_gateway_url');
        $this->privateKey  = config('seb.private_key');
        $this->keyPass     = config('seb.key_pass');
        $this->certificate = config('seb.gateway_certificate');
        $this->orgId       = config('seb.org_id');
    }

    public function getAccountStatement($startDate, $endDate, $iban, $page = 1)
    {
        if ( ! $endDate || ! $startDate || ! $iban) {
            Log::error("Missing endDate or missing startDate: " . json_encode([$startDate, $endDate]));

            return null;
        }

        $today = \Carbon\Carbon::today();
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }

        if ($startDate->gt($endDate) || $endDate->gt($today)) {
            Log::error("Account statement start date cannot be more than end date and end date cannot be in the future. " . json_encode([
                    $startDate,
                    $endDate,
                    $today
                ]));

            return null;
        }

        if ($startDate->diffInDays($endDate) > 31) {
            Log::error("Account statement Can only be queried for maximum one month " . json_encode([
                    $startDate,
                    $endDate,
                    $startDate->diffInDays($endDate)
                ]));

            return null;
        }

        $client = new Client();

        $dataArr = [
            'cert'    => $this->certificate,
            'ssl_key' => [$this->privateKey, $this->keyPass],
            "headers" => [
                "OrgId" => $this->orgId
            ]
        ];

        $queryPart  = "?size=3000&from={$startDate->toDateString()}&to={$endDate->toDateString()}&page=$page";
        $requestUrl = "$this->gatewayUrl/v1/accounts/$iban/transactions$queryPart";

        try {
            $getRes = $client->request("GET", $requestUrl, $dataArr);
        } catch (ClientException $exception) {
            $status = $exception->getResponse()->getStatusCode();
            if ($status == "404") {
                info("No messages right now");

                return null;
            } else {
                throw $exception;
            }
        }

        $responseXml = (string)$getRes->getBody();

        info("Seb returned $responseXml");

        return $responseXml;
    }

}