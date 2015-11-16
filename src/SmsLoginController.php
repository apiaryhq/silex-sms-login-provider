<?php
/**
 * This code is part of the Apiary SMS Login Provider project.
 *
 * (c) Darren Mothersele <darren@darrenmothersele.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Apiary\SmsLoginProvider;

use Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class SmsLoginController
{

    /**
     * An array of countries for the select list options.
     *
     * @var array
     */
    protected $countries;

    /**
     * SMS Handler used to send validate numbers and send SMS verification
     *
     * @var \Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface
     */
    protected $handler;

    /**
     * String used to create verification SMS message, must contain `%s` which
     * is replaced by the verification code.
     *
     * @var string
     */
    protected $messageTemplate;

    /**
     * The name of the view template to use to render the login form.
     * Defaults to 'login.twig'
     *
     * @var string
     */
    protected $viewName;

    protected $debug;

    /**
     * SmsLoginController constructor.
     * @param \Apiary\SmsLoginProvider\SmsHandler\SmsHandlerInterface $handler
     * @param string|NULL $view
     * @param string|NULL $message
     * @param null $debug
     */
    public function __construct(
        SmsHandlerInterface $handler,
        $view = null,
        $message = null
    )
    {
        $this->messageTemplate = $message ?: 'Security code: %s';
        $countryData = file_get_contents(__DIR__ . '/../data/countries.json');
        $this->countries = json_decode($countryData);
        $this->handler = $handler;
        $this->viewName = $view ?: 'login.twig';
    }

    /**
     * Login controller action
     * Step 1: Login shows form to enter mobile phone number.
     *
     * @param \Silex\Application $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function loginAction(Application $app, Request $request)
    {
        return $app['twig']->render($this->viewName, [
            'country_list' => $this->countries,
            'error' => $app['security.last_error']($request),
            'form_action' => $app['url_generator']->generate('sms.login'),
        ]);
    }

    /**
     * Verify controller action
     * Step 2: Send code to number by SMS and show form to verify code
     *
     * @param \Silex\Application $app
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function verifyAction(Application $app, Request $request)
    {
        $mobile = $request->get('mobile');
        $country = $request->get('country');

        if (empty($mobile)) {
            throw new AuthenticationException('Mobile number is required');
        }

        $code = sprintf("%'.04d", rand(0, 9999));
        $app['session']->set('code', $code);

        $number = $this->handler->lookupNumber($mobile, $country);
        $message = sprintf($this->messageTemplate, $code);
        $this->handler->sendSMS($number, $message);

        return $app['twig']->render($this->viewName, [
            'mobile' => $number,
            'form_action' => $app['url_generator']->generate('sms.login') . '/check',
        ]);
    }

}
