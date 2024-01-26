<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Authentication\Adapter;

use ActiveCollab\Authentication\Adapter\Adapter as AuthenticationAdapter;
use ActiveCollab\Authentication\AuthenticatedUser\AuthenticatedUserInterface;
use ActiveCollab\Authentication\AuthenticatedUser\RepositoryInterface as UserRepositoryInterface;
use ActiveCollab\Authentication\AuthenticationResult\AuthenticationResultInterface;
use ActiveCollab\Authentication\AuthenticationResult\Transport\Authentication\AuthenticationTransport;
use ActiveCollab\Authentication\AuthenticationResult\Transport\Deauthentication\DeauthenticationTransport;
use ActiveCollab\Authentication\Exception\InvalidTokenException;
use ActiveCollab\Authentication\Token\RepositoryInterface as TokenRepositoryInterface;
use ActiveCollab\Authentication\Token\TokenInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package Angie\Authentication\Adapter
 */
class AngieTokenHeader extends AuthenticationAdapter
{
    const TOKEN_HEADER_NAME = 'X-Angie-AuthApiToken';

    /**
     * @var UserRepositoryInterface
     */
    private $user_repository;

    /**
     * @var TokenRepositoryInterface
     */
    private $token_repository;

    /**
     * @param UserRepositoryInterface  $user_repository
     * @param TokenRepositoryInterface $token_repository
     */
    public function __construct(UserRepositoryInterface $user_repository, TokenRepositoryInterface $token_repository)
    {
        $this->user_repository = $user_repository;
        $this->token_repository = $token_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ServerRequestInterface $request)
    {
        if (!$request->hasHeader(self::TOKEN_HEADER_NAME)) {
            return null;
        }

        $token_id = trim($request->getHeaderLine(self::TOKEN_HEADER_NAME));

        if ($token_id === null || $token_id === '') {
            throw new InvalidTokenException();
        }

        $bits = explode('-', $token_id);

        if (count($bits) < 2) {
            throw new InvalidTokenException();
        }

        if (!ctype_digit($bits[0])) {
            throw new InvalidTokenException();
        }

        $user_id = (int) array_shift($bits);
        $token_string = implode('-', $bits);

        if ($token = $this->token_repository->getById($token_string)) {
            if ($user = $token->getAuthenticatedUser($this->user_repository)) {
                if ($user_id != $user->getId()) {
                    throw new InvalidTokenException();
                }

                $this->token_repository->recordUsageByToken($token);

                return new AuthenticationTransport($this, $user, $token);
            }
        }

        throw new InvalidTokenException();
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(AuthenticatedUserInterface $authenticated_user, array $credentials = [])
    {
        return $this->token_repository->issueToken($authenticated_user, $credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(AuthenticationResultInterface $authenticated_with)
    {
        if (!$authenticated_with instanceof TokenInterface) {
            throw new InvalidArgumentException('Instance is not a token');
        }

        $this->token_repository->terminateToken($authenticated_with);

        return new DeauthenticationTransport($this);
    }
}
