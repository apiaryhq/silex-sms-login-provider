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
        $numbers = $this->getMockBuilder('Services_Twilio_Rest_Lookups_PhoneNumbers')
            ->setConstructorArgs(['sid', 'auth'])->getMock();

        $example = new \stdClass();
        $example->phone_number = '+15005550000';
        $numbers->expects($this->any())->method('get')->willReturn($example);
        $lookup->phone_numbers = $numbers;
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

    public function testLookupNumber()
    {
        $number = $this->handler->lookupNumber('+15005550000', 'gb');
        $this->assertEquals('+15005550000', $number);
    }

}
