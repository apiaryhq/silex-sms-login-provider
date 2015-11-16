<?php

namespace Apiary\SmsLoginProvider\Test;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MockUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        echo 'looking for ' . $username . PHP_EOL;
        return new MockUser();
    }

    public function refreshUser(UserInterface $user)
    {
    }

    public function supportsClass($class)
    {
    }

}
