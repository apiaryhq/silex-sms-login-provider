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

class MockSmsHandlerProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['sms.handler'] = $app->share(function () use ($app) {
            return new MockSmsHandler($app);
        });
    }

    public function boot(Application $app)
    {
        // Do nothing
    }
}
