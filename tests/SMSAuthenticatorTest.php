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

use Apiary\SmsLoginProvider\SmsAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class SmsAuthenticatorTest extends \PHPUnit_Framework_TestCase {

  public function testSupportsCorrectToken() {
    $token = new UsernamePasswordToken('username', 9999, 'test');
    $authenicator = new SmsAuthenticator('test', 9999, null);
    $this->assertTrue($authenicator->supports($token));
  }
}
