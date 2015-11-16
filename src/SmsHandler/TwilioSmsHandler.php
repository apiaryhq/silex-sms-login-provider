<?php
/**
 * This file is part of the Apiary SMS Login Provider package.
 *
 * (c) Darren Mothersele <darren@darrenmothersele.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Apiary\SmsLoginProvider\SmsHandler;


use Apiary\SmsLoginProvider\Exception\InvalidPhoneNumberException;
use Apiary\SmsLoginProvider\Exception\SmsSendFailException;
use Lookups_Services_Twilio;
use Services_Twilio_RestException;
use Services_Twilio;

class TwilioSmsHandler implements SmsHandlerInterface
{

    private $lookupClient;
    private $client;
    private $from = '';

    /**
     * TwilioSMSHandler constructor.
     * @param Lookups_Services_Twilio $lookupClient
     * @param Services_Twilio $client
     */
    public function __construct(Lookups_Services_Twilio $lookupClient, Services_Twilio $client)
    {
        $this->client = $client;
        $this->lookupClient = $lookupClient;
    }

    public function setDefaultFrom($from)
    {
        $this->from = $from;
    }


    public function lookupNumber($number, $countryCode)
    {
        try {
            $number = $this->lookupClient->phone_numbers->get($number,
                ['CountryCode' => $countryCode]);
        } catch (Services_Twilio_RestException $e) {
            throw new InvalidPhoneNumberException($e->getMessage());
        }
        return $number->phone_number;
    }

    public function sendSMS($to, $body, $from = null)
    {
        if ($from === null) {
            $from = $this->from;
        }
        try {
            $sms = $this->client->account->messages->create([
                'From' => $from,
                'To' => $to,
                'Body' => $body,
            ]);
        } catch (Services_Twilio_RestException $e) {
            throw new SMSSendFailException($e->getMessage());
        }
        return $sms->sid;
    }
}
