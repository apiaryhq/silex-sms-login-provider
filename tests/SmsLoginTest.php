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

use Apiary\SmsLoginProvider\SmsLoginProvider;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\BrowserKit\Cookie;

class SmsLoginTest extends WebTestCase {

  public function createApplication() {
    $app = new Application();
    $app['debug'] = true;
//    $app['session.test'] = true;
    $app['sms.debug'] = true;
    $app['monolog.logfile'] = __DIR__.'/../build/logs/dev.monolog.log';
    $app['session.storage'] = new MockFileSessionStorage();

    $app->register(new Provider\TwigServiceProvider);
    $app->register(new Provider\ServiceControllerServiceProvider);
    $app->register(new Provider\SecurityServiceProvider);
    $app->register(new Provider\SessionServiceProvider);
    $app->register(new Provider\UrlGeneratorServiceProvider);
    $app->register(new Provider\MonologServiceProvider);

    $handler = $this->getMock('Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface');
    $handler->method('lookupNumber')->willReturn('+15005550000');
    $handler->method('sendSMS')->willReturn(1);

    $app['sms.handler'] = $app->share(function () use ($handler) {
      return $handler;
    });

    $userManager = $this->getMock('Symfony\Component\Security\Core\User\UserProviderInterface');
    $app['user.manager'] = $app->share(function () use ($userManager) {
      return $userManager;
    });

    $smsLoginProvider = new SmsLoginProvider();
    $app->register($smsLoginProvider);
    $app->mount('', $smsLoginProvider);

    $app->get('/', function () {
      return $app['twig']->render('home.twig', [
        'message' => 'Testing',
      ]);
    });

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
      'login.twig' => '<form action="{{ form_action }}">{% if mobile is defined %}<p class="number">{{ mobile }}</p>{% endif %}</form>',
      'home.twig' => 'Homepage {{ message }}',
    ];
    unset($app['exception_handler']);
    return $app;
  }

  public function testGetMobileForm() {
    $client = $this->createClient();
    $crawler = $client->request('GET', '/login');
    $this->assertTrue($client->getResponse()->isOk());
    $this->assertTrue($client->getResponse()->isOk());
    $this->assertCount(1, $crawler->filter('form[action="/login"]'));
  }

  public function testPostMobileForm() {

    $client = $this->createClient();

    $crawler = $client->request('POST', '/login', ['mobile' => '+15005550000']);
    $this->assertTrue($client->getResponse()->isOk());
    $this->assertCount(1, $crawler->filter('form[action="/login/check"]'));

    $numberText = $crawler->filter('.number')->text();
    preg_match("/\((\d\d\d\d)\)/", $numberText, $code);
    $code = $code[1];
    $this->assertRegExp('/\d\d\d\d/', $code);

  }
  
}
