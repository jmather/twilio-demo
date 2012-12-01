<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class TwilioController implements \Silex\ControllerProviderInterface
{

    public function connect(Application $app)
    {
        /** @var $controller \Silex\ControllerCollection */
        $controller = $app['controllers_factory'];

        $controller->post('/sms-in', function() use ($app) {
            $output = $app['twilio.helper']->processInboundSms();

            return new Response($output, 200);
        })->bind('sms_in');

        $controller->post('/sms-in-phone-script/{message}', function($message) use ($app) {
            $output = $app['twilio.helper']->processInboundSmsPhoneScript($message);

            return new Response($output, 200);
        })->bind('sms_in_phone_script');

        $controller->post('/sms-in-phone-script-reply/thank-you', function() use ($app)
        {
            $output = $app['twilio.helper']->processInboundSmsPhoneScriptThankYou();

            return new Response($output, 200);
        })->bind('sms_in_phone_script_thank_you');

        $controller->post('/sms-in-phone-script-reply/transcript/{reply_to}', function($reply_to) use ($app) {
            $output = $app['twilio.helper']->processInboundSmsPhoneTranscript($reply_to);

            return new Response($output, 200);
        })->bind('sms_in_phone_script_transcript');

        return $controller;
    }
}
