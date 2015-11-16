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

use Apiary\SmsLoginProvider\SMSHandler\MockSmsHandlerProvider;
use Silex\WebTestCase;
use Silex\Application;
use Silex\Provider;

use Apiary\SmsLoginProvider\SmsLoginProvider;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class SmsLoginTest extends WebTestCase
{

    public function createApplication()
    {
        $app = new Application();
        $app['debug'] = true;
        $app['session.test'] = true;
        $app['monolog.logfile'] = __DIR__ . '/../build/logs/dev.monolog.log';
        $app['session.storage'] = new MockFileSessionStorage();

        $app->register(new Provider\TwigServiceProvider);
        $app->register(new Provider\ServiceControllerServiceProvider);
        $app->register(new Provider\SecurityServiceProvider);
        $app->register(new Provider\SessionServiceProvider);
        $app->register(new Provider\UrlGeneratorServiceProvider);
        $app->register(new Provider\MonologServiceProvider);

        // Mock services
        $app->register(new MockSmsHandlerProvider());
        $app['user.manager'] = $app->share(function () {
            return new MockUserProvider();
        });

        $smsLoginProvider = new SmsLoginProvider();
        $app->register($smsLoginProvider);
        $app->mount('', $smsLoginProvider);

        $app->get('/', function () use ($app) {
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
            'users' => $app->share(function ($app) {
                return $app['user.manager'];
            }),
          ),
        );
        $app['twig.templates'] = [
          'login.twig' => '<form action="{{ form_action }}" method="POST">{% if mobile is defined %}<p class="number">{{ mobile }}</p>'
              .'<input type="hidden" name="mobile" value="{{ mobile }}" />{% endif %}'
              .'<input type="password" placeholder="Secret code" name="code" />'
              .'<button class="btn btn-positive btn-block" name="submit">Login</button></form>',
          'home.twig' => 'Homepage {{ message }}',
        ];
        unset($app['exception_handler']);
        return $app;
    }

    public function testGetMobileForm()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/login');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('form[action="/login"]'));
    }

    public function testPostMobileForm()
    {

        $client = $this->createClient();
        $crawler = $client->request('POST', '/login', ['mobile' => '+15005550000']);

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('form[action="/login/check"]'));

        $numberText = $crawler->filter('.number')->text();
        preg_match("/\((\d\d\d\d)\)/", $numberText, $code);
        $code = $code[1];
        $this->assertRegExp('/\d\d\d\d/', $code);

    }

    public function testLoggedInArea()
    {
        $client = $this->createClient();
        $session = $this->app['session'];

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('+15005550000', null, $firewall, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewall, serialize($token));

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }


}
