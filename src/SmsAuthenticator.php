<?php

namespace Apiary\SmsLoginProvider;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class SmsAuthenticator implements AuthenticationProviderInterface {

  private $code;
  private $logger;
  private $name;

  public function __construct($name, $code, $logger) {
    $this->code = $code;
    $this->logger = $logger;
    $this->name = $name;
  }

  public function supports(TokenInterface $token) {
    return $token instanceof UsernamePasswordToken
    && $token->getProviderKey() === $this->name;
  }

  public function authenticate(TokenInterface $token) {
    if ($this->code != $token->getCredentials()) {
      throw new AuthenticationException("Authentication code does not match");
    }
    $user = $token->getUser();
    // TODO: Provide a mechanism to get the real user object, and set user roles in token
    $token = new UsernamePasswordToken($user, $token->getCredentials(), $this->name, ['ROLE_USER']);
    return $token;
  }
}
