<?php

namespace Paytic\Omnipay\Euplatesc\Tests\Message;

use Paytic\Omnipay\Euplatesc\Message\PurchaseRequest;
use Paytic\Omnipay\Euplatesc\Message\PurchaseResponse;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Http\Client;

/**
 * Class PurchaseRequestTest
 * @package Paytic\Omnipay\Euplatesc\Tests\Message
 */
class PurchaseRequestTest extends AbstractRequestTest
{
    public function testInitParameters()
    {
        $data = [
            'mid' => 222,
            'key' => 333,
            'endpointUrl' => 444,
        ];
        $request = $this->newRequestWithInitTest(PurchaseRequest::class, $data);
        self::assertEquals($data['mid'], $request->getMid());
        self::assertEquals($data['key'], $request->getKey());
        self::assertEquals($data['endpointUrl'], $request->getEndpointUrl());
    }

    public function testSendWithMissingAmount()
    {
        $data = [
            'mid' => 111,
            'key' => 333,
            'card' => [
                'first_name' => '',
            ],
            'endpointUrl' => 444,
        ];
        $request = $this->newRequestWithInitTest(PurchaseRequest::class, $data);

        self::expectException(InvalidRequestException::class);
        self::expectExceptionMessage('The amount parameter is required');
        $request->send();
    }

    public function testSend()
    {
        $data = [
            'mid' => getenv('EUPLATESC_MID'),
            'key' => getenv('EUPLATESC_KEY'),
            'orderId' => '99999897987987987987987',
            'orderName' => 'Test tranzaction 9999999999',
            'notifyUrl' => 'http://localhost',
            'returnUrl' => 'http://localhost',
            'endpointUrl' => 'https://secure.euplatesc.ro/tdsprocess/tranzactd.php',
            'card' => [
                'first_name' => '',
            ],
            'amount' => 20.00,
            'currency' => 'RON',
        ];
        $this->sendValidation($data);
    }

    public function testSendWithSpecialCharacters()
    {
        $data = [
            'mid' => getenv('EUPLATESC_MID'),
            'key' => getenv('EUPLATESC_KEY'),
            'orderId' => "999998'!@#$%^&*()97987987987987987",
            'orderName' => "Test tranzaction 9999999999'!@#$%^&*()",
            'notifyUrl' => 'http://localhost',
            'returnUrl' => 'http://localhost',
            'endpointUrl' => 'https://secure.euplatesc.ro/tdsprocess/tranzactd.php',
            'card' => [
                'first_name' => '',
            ],
            'amount' => 20.00,
            'currency' => 'RON',
        ];
        $this->sendValidation($data);
    }

    /**
     * @param $data
     */
    protected function sendValidation($data)
    {
        $request = $this->newRequestWithInitTest(PurchaseRequest::class, $data);

        /** @var PurchaseResponse $response */
        $response = $request->send();
        self::assertInstanceOf(PurchaseResponse::class, $response);

        $redirectData = $response->getRedirectData();
        self::assertCount(19, $redirectData);

        $client = new Client();
        $gatewayResponse = $client->request(
            'POST',
            $response->getRedirectUrl(),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($redirectData, null, '&')
        );

        self::assertSame(200, $gatewayResponse->getStatusCode());
//        self::assertTrue(strpos($gatewayResponse->getEffectiveUrl(), 'secure.euplatesc.ro') !== false);

        //Validate first Response
        $body = strtolower($gatewayResponse->getBody(true));
        self::stringContains("<meta http-equiv='refresh' content=", $body);

        if (preg_match('/\<meta[^\>]+http-equiv=\'refresh\' content=\'.*?url=(.*?)\'/i', $body, $matches)) {
            $url = $matches[1];
            $gatewayResponse = $client->request('GET', $url);
            $body = $gatewayResponse->getBody()->getContents();
        }

        self::stringContains('Num&#259;r comand&#259;:', $body);
        self::stringContains('Descriere comand&#259;:', $body);
        self::stringContains(number_format($data['amount'], 2) . ' LEI', $body);
    }
}
