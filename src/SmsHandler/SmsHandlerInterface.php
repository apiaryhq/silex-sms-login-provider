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

/**
 * Interface SmsHandlerInterface
 * @package Apiary
 */
interface SmsHandlerInterface
{


    /**
     * Set the default From address or number
     * @param string $from
     */
    public function setDefaultFrom($from);

    /**
     * Lookup, validate a number and convert to E.164
     * @param string $number Phone number in any format
     * @param string $countryCode ISO Country code
     * @return string Validated number in E.164 format
     * @throws Apiary\SmsLoginProvider\Exception\InvalidPhoneNumberException
     */
    public function lookupNumber($number, $countryCode);


    /**
     * Send a single SMS message
     * @param $from
     * @param $to
     * @param $body
     * @return string identifier of sent message
     * @throws Apiary\SmsLoginProvider\Exception\SMSSendFailException
     */
    public function sendSMS($to, $body, $from = null);

}