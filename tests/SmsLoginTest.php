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

use Silex\WebTestCase;
use Silex\Application;
use Silex\Provider;

use Apiary\SmsLoginProvider\SmsHandler\TwilioSmsHandlerProvider;
use Apiary\SmsLoginProvider\SmsLoginProvider;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SmsLoginTest extends WebTestCase {

  public function createApplication() {
    $app = new Application();
    $app['debug'] = true;
    $app['session.test'] = true;
    $app['sms.debug'] = true;
    $app['monolog.logfile'] = __DIR__.'/../build/logs/dev.monolog.log';
    $app['session.storage'] = new MockArraySessionStorage();

    $app->register(new Provider\TwigServiceProvider);
    $app->register(new Provider\ServiceControllerServiceProvider);
    $app->register(new Provider\SecurityServiceProvider);
    $app->register(new Provider\SessionServiceProvider);
    $app->register(new Provider\UrlGeneratorServiceProvider);
    $app->register(new Provider\MonologServiceProvider);

    $accountSid = 'TWILIO_ACCOUNT_SID';
    $authToken = 'TWILIO_AUTH_TOKEN';
    $app->register(new TwilioSMSHandlerProvider(), [
      'sms.handler.from' => 'Test',
      'sms.handler.twilio_sid' => $accountSid,
      'sms.handler.twilio_auth_token' => $authToken,
    ]);

    $userManager = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
    $app['user.manager'] = $app->share(function () use ($userManager) {
      return $userManager;
    });

    $smsLoginProvider = new SmsLoginProvider();
    $app->register($smsLoginProvider);
    $app->mount('', $smsLoginProvider);

    $app['security.firewalls'] = array(
      // Login page is accessible to all:
      'login' => array(
        'pattern' => '^/login$',
      ),
      // Everything else is secured:
      'secured_area' => array(
        'pattern' => '^.*$',
        'sms' => true,
        'logout' => ['logout_path' => '/logout'],
        'users' => $app->share(function($app) { return $app['user.manager']; }),
      ),
    );
    $app['twig.templates'] = [
      'login.twig' => '<form></form>',
    ];
    unset($app['exception_handler']);
    return $app;
  }

  public function testEnterMobileForm() {
    $client = $this->createClient();
    $crawler = $client->request('GET', '/login');
    $this->assertTrue($client->getResponse()->isOk());
    $this->assertCount(1, $crawler->filter('form'));
  }


}
