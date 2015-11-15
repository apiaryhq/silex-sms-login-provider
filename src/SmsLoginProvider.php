<?php
/**
 * This file is part of the Apiary SMS Login Provider package
 *
 * (c) Darren Mothersele <darren@darrenmothersele.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Apiary\SmsLoginProvider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\Security\Http\EntryPoint\FormAuthenticationEntryPoint;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;


class SmsLoginProvider implements ServiceProviderInterface, ControllerProviderInterface {

  public function connect(Application $app) {

    $controllers = $app['controllers_factory'];

    $controllers->get('/login', 'login.controller:loginAction')
      ->bind('sms.login');
    $controllers->post('/login', 'login.controller:verifyAction');

    $app->error(function (\Services_Twilio_RestException $e) use ($app) {
      $app['session']->set(Security::AUTHENTICATION_ERROR, new BadCredentialsException('Invalid phone number'));
      return $app->redirect($app['url_generator']->generate('sms.login'));
    });

    return $controllers;

  }

  public function register(Application $app) {
    $app['login.controller'] = $app->share(function () use ($app) {
      return new SmsLoginController($app['sms.handler']);
    });

    $app['security.authentication_listener.factory.sms'] = $app->protect(function ($name, $options) use ($app) {

      $app['security.entry_point.'.$name.'.sms'] = $app->share(function () use ($app, $options) {
        $loginPath = $app['url_generator']->generate('sms.login');
        $useForward = isset($options['use_forward']) ? $options['use_forward'] : false;
        return new FormAuthenticationEntryPoint($app, $app['security.http_utils'], $loginPath, $useForward);
      });

      $app['security.authentication_provider.'.$name.'.sms'] = $app->share(function () use ($app, $name) {
        return new SMSAuthenticator($name, $app['session']->get('code'), $app['monolog']);
      });

      $app['security.authentication_listener.'.$name.'.sms'] = $app->share(function () use ($app, $name, $options) {

        // Create fake route for login check
        $loginCheckPath = $app['url_generator']->generate('sms.login') . '/check';
        $options['check_path'] = $loginCheckPath;
        $app->match($loginCheckPath)->run(null)->bind(str_replace('/', '_', ltrim($loginCheckPath, '/')));

        // Set default form item names, if not provided
        $options['username_parameter'] = empty($options['username_parameter']) ? 'mobile': $options['username_parameter'];
        $options['password_parameter'] = empty($options['password_parameter']) ? 'code' : $options['password_parameter'];

        if (!isset($app['security.authentication.success_handler.'.$name])) {
          $app['security.authentication.success_handler.'.$name] = $app->share(function () use ($name, $options, $app) {
            $handler = new DefaultAuthenticationSuccessHandler($app['security.http_utils'], $options);
            $handler->setProviderKey($name);
            return $handler;
          });
        }

        if (!isset($app['security.authentication.failure_handler.'.$name])) {
          $app['security.authentication.failure_handler.'.$name] = $app->share(function () use ($name, $options, $app) {
            return new DefaultAuthenticationFailureHandler($app, $app['security.http_utils'], $options, $app['logger']);
          });
        }

        return new UsernamePasswordFormAuthenticationListener(
          $app['security.token_storage'],
          $app['security.authentication_manager'],
          isset($app['security.session_strategy.'.$name]) ? $app['security.session_strategy.'.$name] : $app['security.session_strategy'],
          $app['security.http_utils'],
          $name,
          $app['security.authentication.success_handler.'.$name],
          $app['security.authentication.failure_handler.'.$name],
          $options,
          $app['logger'],
          $app['dispatcher'],
          isset($options['with_csrf']) && $options['with_csrf'] && isset($app['form.csrf_provider']) ? $app['form.csrf_provider'] : null
        );

      });

      return [
        // the authentication provider id
        'security.authentication_provider.'.$name.'.sms',
        // the authentication listener id
        'security.authentication_listener.'.$name.'.sms',
        // the entry point id
        'security.entry_point.'.$name.'.sms',
        // the position of the listener in the stack
        'form'
      ];
    });
  }

  public function boot(Application $app) {

  }

}