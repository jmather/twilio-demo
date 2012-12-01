<?php

use Symfony\Component\HttpKernel\HttpKernel;

class TwilioControllerTest extends Silex\WebTestCase
{

    /**
     * Creates the application.
     *
     * @return HttpKernel
     */
    public function createApplication()
    {

        $app = new \Silex\Application();

        $app->mount('/', new TwilioController());

        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function testSmsIn()
    {
        $helper = $this->getMockBuilder('TwilioHelper')->disableOriginalConstructor()->getMock();
        $helper->expects($this->once())->method('processInboundSms');

        $this->app['twilio.helper'] = $helper;

        $client = $this->createClient();

        $params = array(
            'AccountSid' => 'ACda81ea98e3a44c3e8e07d2812e0392c9',
            'ApiVersion' => '2010-04-01',
            'Body' => 'This is a test',
            'From' => '+15178033009',
            'FromCity' => 'LANSING',
            'FromCountry' => 'US',
            'FromState' => 'MI',
            'FromZip' => '48915',
            'SmsMessageSid' => 'SMeedee8da8e3817544a08208e979d83c0',
            'SmsSid' => 'SMeedee8da8e3817544a08208e979d83c0',
            'SmsStatus' => 'received',
            'To' => '+15179170785',
            'ToCity' => 'JACKSON',
            'ToCountry' => 'US',
            'ToState' => 'MI',
            'ToZip' => '49203'
        );

        $client->request('POST', '/sms-in', $params);
    }
}
