<?php

namespace Yoye\OVH;

use Guzzle\Common\Event;
use Guzzle\Http\Client as BaseClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Plugin implements EventSubscriberInterface
{

    /**
     * @var string
     */
    private $applicationKey;

    /**
     * @var string
     */
    private $applicationSecret;

    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var integer
     */
    private $latence;

    public function __construct($applicationKey, $applicationSecret, $consumerKey)
    {
        $this->applicationKey    = $applicationKey;
        $this->applicationSecret = $applicationSecret;
        $this->consumerKey       = $consumerKey;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('onRequestBeforeSend'),
        );
    }

    public function onRequestBeforeSend(Event $event)
    {
        $request             = $event['request'];
        $timestamp           = $this->getTimestamp($request->getClient());
        $signature           = '$1$';
        $body                = method_exists($request, 'getBody') ? $request->getBody() : '';
        $signatureParameters = array(
            $this->applicationSecret,
            $this->consumerKey,
            $request->getMethod(),
            $request->getUrl(),
            $body,
            $timestamp,
        );

        $signature .= sha1(implode('+', $signatureParameters));

        $request->setHeader('X-Ovh-Application', $this->applicationKey);
        $request->setHeader('X-Ovh-Timestamp', $timestamp);
        $request->setHeader('X-Ovh-Signature', $signature);
        $request->setHeader('X-Ovh-Consumer', $this->consumerKey);
    }

    private function getTimestamp(BaseClient $client)
    {
        if (null === $this->latence) {
            $this->setLatence($client);
        }

        return time() - $this->latence;
    }

    private function setLatence(BaseClient $client)
    {
        $latenceClient = new BaseClient($client->getBaseUrl());
        $response      = $latenceClient->get('auth/time')->send();
        $this->latence = time() - (int) $response->getBody(true);
    }

}