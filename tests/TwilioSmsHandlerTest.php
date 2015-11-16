<?php


namespace Apiary\SmsLoginProvider\Test;

use Apiary\SmsLoginProvider\SmsHandler\TwilioSmsHandler;

class TwilioSmsHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $handler;

    protected function setUp()
    {
        parent::setUp();

        $lookup = $this->getMockBuilder('Lookups_Services_Twilio')
            ->setConstructorArgs(['sid', 'auth'])->getMock();
        $client = $this->getMockBuilder('Services_Twilio')
            ->setConstructorArgs(['sid', 'auth'])->getMock();
        $this->handler = new TwilioSmsHandler($lookup, $client);
    }


    public function testConstruction()
    {
        $this->assertNotEmpty($this->handler);
    }

    public function testSetDefaultFrom()
    {
        $this->handler->setDefaultFrom('TEST');
    }


}
