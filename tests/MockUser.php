<?php
namespace Apiary\SmsLoginProvider\Test;


use Symfony\Component\Security\Core\User\UserInterface;

class MockUser implements UserInterface
{
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return '9999';
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return '+15005550000';
    }

    public function eraseCredentials()
    {
    }

}
