<?php

use Symfony\Component\Routing\Generator\UrlGenerator;
use Monolog\Logger;

class TwilioHelper
{
    private $twilio;
    private $number;
    private $url_generator;
    private $logger;

    public function __construct(Majax_Twilio_Client $twilio, $number, UrlGenerator $url_generator, Logger $logger)
    {
        $this->twilio = $twilio;
        $this->number = $number;
        $this->url_generator = $url_generator;
        $this->logger = $logger;
    }

    public function processInboundSms()
    {
        if (false == $this->twilio->validateRequest())
            return '';

        $response = $this->twilio->parseTextResponse();

        $from = $response->getFrom();
        $message = $response->getBody();

        $url = $this->url_generator->generate('sms_in_phone_script', array('message' => base64_encode($message)), true);

        $call = $this->twilio->createCall($this->number, $from, $url);

        $call->send();

        $twiml = new Majax_Twilio_Twiml();

        return $twiml->toString();
    }

    public function processInboundSmsPhoneScript($base64_message)
    {
        if (false == $this->twilio->validateRequest())
            return '';

        $response = $this->twilio->parseCallResponse();

        $content = base64_decode($base64_message);

        $twml = new Majax_Twilio_Twiml();

        $twml->say($content);

        $twml->say('After the beep, please wait a moment, and then say something.');

        $processing_url = $this->url_generator->generate('sms_in_phone_script_thank_you');
        $transcript_url = $this->url_generator->generate('sms_in_phone_script_transcript', array('reply_to' => base64_encode($response->getTo())));

        $twml->record($processing_url, null, null, null, $transcript_url, true);

        $twml->hangup();

        return $twml->toString();
    }

    public function processInboundSmsPhoneScriptThankYou()
    {
        if (false == $this->twilio->validateRequest())
            return '';

        $twml = new Majax_Twilio_Twiml();

        $twml->say('Thank you. Expect a text message shortly.');

        $twml->hangup();

        return $twml->toString();
    }

    public function processInboundSmsPhoneTranscript($base64_reply_to)
    {
        if (false == $this->twilio->validateRequest())
            return '';

        $transcript = $this->twilio->parseTranscriptResponse();

        if ($transcript->getTranscriptionStatus() == 'completed')
        {
            $reply_to = base64_decode($base64_reply_to);
            if ($transcript->getTranscriptionText() != '(blank)')
                $this->twilio->createText($this->number, $reply_to, $transcript->getTranscriptionText())->send();
            else
                $this->twilio->createText($this->number, $reply_to, 'We were unable to transcribe your message. Sorry.')->send();

            $this->twilio->deleteRecording($transcript->getRecordingSid());
        }

        $twml = new Majax_Twilio_Twiml();

        return $twml->toString();
    }
}
