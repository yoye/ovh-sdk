<?php

namespace Yoye\OVH;

use Guzzle\Service\Client as ServiceClient;
use Guzzle\Service\Description\ServiceDescription;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Client extends ServiceClient
{

    public static function factory($config = array())
    {
        $resolver = new OptionsResolver();
        static::setDefaultOptions($resolver);

        $config = $resolver->resolve($config);

        $client = new self($config['base_url'], array(
            'request.options' => array(
                'headers' => array('Content-Type' => 'application/json'),
            )
        ));
        $client->addSubscriber(new Plugin($config['application_key'], $config['application_secret'], $config['consumer_key']));
        $client->setDescription(ServiceDescription::factory(realpath(__DIR__ . '/../../../config/description.json')));

        return $client;
    }

    protected static function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
                'application_key',
                'application_secret',
                'consumer_key',
            ))
            ->setDefaults(array(
                'base_url' => 'https://api.ovh.com/1.0/'
            ))
            ->setAllowedValues(array(
                'base_url' => array(
                    'https://eu.api.ovh.com/1.0/',
                    'https://ca.api.ovh.com/1.0/',
                    'https://api.ovh.com/1.0/',
                )
        ));
    }

}