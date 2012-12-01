<?php

/** @var $app \Silex\Application */

require __DIR__.'/config.php';

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../log/server.log',
));

$app['twilio'] = function() use ($app) {
    return new Majax_Twilio_Client($app['twilio.sid'], $app['twilio.token']);
};

$app['twilio.helper'] = function() use ($app) {
    return new TwilioHelper($app['twilio'], $app['twilio.number'], $app['url_generator'], $app['monolog']);
};

$app->mount('/', new TwilioController());
