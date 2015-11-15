<?php
/**
 * This file is part of the Apiary SMS Login Provider package.
 *
 * (c) Darren Mothersele <darren@darrenmothersele.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Apiary\SmsLoginProvider\SMSHandler;


use Apiary\SmsLoginProvider\Exception\InvalidPhoneNumberException;
use Apiary\SmsLoginProvider\Exception\SmsSendFailException;
use Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface;
use Lookups_Services_Twilio;
use Services_Twilio_RestException;
use Services_Twilio;

class TwilioSMSHandler implements SmsHandlerInterface
{

    private $accountSid;
    private $authToken;
    private $client;
    private $from = '';

    /**
     * TwilioSMSHandler constructor.
     * @param $accountSid
     * @param $authToken
     */
    public function __construct($accountSid, $authToken)
    {
        $this->accountSid = $accountSid;
        $this->authToken = $authToken;
        $this->client = new Services_Twilio($this->accountSid, $this->authToken);
    }

    public function setDefaultFrom($from)
    {
        $this->from = $from;
    }


    public function lookupNumber($number, $countryCode)
    {
        $lookupClient = new \Lookups_Services_Twilio($this->accountSid, $this->authToken);
        try {
            $number = $lookupClient->phone_numbers->get($number, ['CountryCode' => $countryCode]);
        }
        catch (Services_Twilio_RestException $e) {
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
        }
        catch (Services_Twilio_RestException $e) {
            throw new SMSSendFailException($e->getMessage());
        }
        return $sms->sid;
    }
}
