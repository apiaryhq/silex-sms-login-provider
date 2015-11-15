<?php

namespace Apiary\SmsLoginProvider;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class SMSAuthenticator implements AuthenticationProviderInterface {

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
    return $token;
  }
}