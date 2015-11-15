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

use Silex\Application;
use Silex\ServiceProviderInterface;

class TwilioSmsHandlerProvider implements ServiceProviderInterface {

  public function register(Application $app) {
    if (!isset($app['sms.handler.from'])) {
      $app['sms.handler.from'] = 'Apiary';
    }
    $app['sms.handler'] = $app->share(function () use ($app) {
      return new TwilioSmsHandler($app['sms.handler.twilio_sid'],
        $app['sms.handler.twilio_auth_token']);
    });
  }

  public function boot(Application $app) {
    $app['sms.handler']->setDefaultFrom($app['sms.handler.from']);
  }

}
