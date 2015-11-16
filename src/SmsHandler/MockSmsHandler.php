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


use Silex\Application;

class MockSmsHandler implements SmsHandlerInterface
{
    protected $from;
    protected $container;

    public function __construct(Application $app)
    {
        $this->container = $app;
    }

    public function setDefaultFrom($from)
    {
        $this->from = $from;
    }

    public function lookupNumber($number, $countryCode)
    {
        $code = $this->container['session']->get('code');
        $fakeNumber = "+15005550000 ({$code})";
        return $fakeNumber;
    }

    public function sendSMS($to, $body, $from = null)
    {
        $fakeSid = 1;
        return $fakeSid;
    }

}