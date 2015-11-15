<?php
/**
 * This file is part of the silex-sms-login-provider project.
 *
 * (c) Darren Mothersele <darren@darrenmothersele.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Apiary\SmsLoginProvider\Test;


use Apiary\SmsLoginProvider\SmsLoginController;

class SmsLoginControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruction()
    {
        $handler = $this->getMock('Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface');
        $controller = new SmsLoginController($handler);
        $this->assertNotEmpty($controller);
    }

    // TODO: Test controller actions
}
