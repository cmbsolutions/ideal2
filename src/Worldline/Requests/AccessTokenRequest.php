<?php declare(strict_types=1);

namespace POM\iDEAL\Wordline\Requests;

use DateInterval;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Wordline\Resources\AccessSignature;
use POM\iDEAL\Wordline\iDEAL;
use POM\iDEAL\Wordline\Resources\AccessToken;

readonly class AccessTokenRequest
{
    public function __construct(private iDEAL $iDEAL)
    {
    }

    public function execute(): AccessToken
    {
        $client = new Client();

        $dateTime = new DateTime('now', new DateTimeZone('UTC'));

        // Get the offset for the specified timezone (here assuming CET/CEST)
        $timezone = new DateTimeZone('Europe/Paris');
        $offset = $timezone->getOffset($dateTime);
        $dateTime->modify('+' . $offset . ' seconds');
        $accessSignature = new AccessSignature($this->iDEAL, $dateTime);

        $headers = [
            'App' => $this->iDEAL->getConfig()->getBank()->getApp(),
            'Client' => $this->iDEAL->getConfig()->getBank()->getClient(),
            'Id' => $this->iDEAL->getConfig()->getMerchantId(),
            'Date' => $dateTime->format(DATE_ATOM),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'Authorization' => $accessSignature->getSignature(),
        ];

        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials'
        ]];

        $request  = new Request('POST', $this->iDEAL->getConfig()->getBaseUrl() . '/xs2a/routingservice/services/authorize/token', $headers);

        $response = $client->send($request, $options);

        $responseBody = $response->getBody()->getContents();
        $response = json_decode($responseBody);

        $expireDateTime = new DateTime();
        $expireDateTime->add(new DateInterval('PT' . $response->expires_in . 'S'));

        return new AccessToken($response->access_token, $expireDateTime);
    }

}