<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\Authentication\RequestProcessor;

use ActiveCollab\Authentication\Authorizer\RequestProcessor\RequestProcessingResult\RequestProcessingResult;
use ActiveCollab\Authentication\Authorizer\RequestProcessor\RequestProcessorInterface;
use ActiveCollab\Authentication\Saml\SamlUtils;
use ActiveCollab\Authentication\Session\SessionInterface;
use ActiveCollab\CurrentTimestamp\CurrentTimestampInterface;
use Angie\Http\Response\MovedResource\MovedResource;
use AngieApplication;
use InvalidArgumentException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class ShepherdRequestProcessor implements RequestProcessorInterface
{
    const INTENT_ISSUER_SHEPHERD = 'https://accounts.activecollab.com';
    const INTENT_AUDIENCE_ACTIVECOLLAB = 'https://app.activecollab.com';

    /**
     * @var SamlUtils
     */
    private $saml_utils;

    /**
     * @var Parser
     */
    private $jwt_parser;

    /**
     * @var Sha256
     */
    private $jwt_signer;

    /**
     * @var CurrentTimestampInterface
     */
    private $current_timestamp;

    public function __construct(CurrentTimestampInterface $current_timestamp)
    {
        $this->jwt_parser = new Parser();
        $this->jwt_signer = new Sha256();
        $this->saml_utils = new SamlUtils();
        $this->current_timestamp = $current_timestamp;
    }

    public function processRequest(ServerRequestInterface $request)
    {
        $payload = null;
        $parsed_body_data = $request->getParsedBody();

        if ($this->isIntentRequest($request)) {
            $client_vendor = isset($parsed_body_data['client_vendor']) ? $parsed_body_data['client_vendor'] : null;
            $client_name = isset($parsed_body_data['client_name']) ? $parsed_body_data['client_name'] : null;

            try {
                $token = $this->jwt_parser->parse($parsed_body_data['intent']);
            } catch (InvalidArgumentException $e) {
                if ($e->getMessage() == 'The JWT string must have two dots') {
                    AngieApplication::log()->notice(
                        'Client has tried to authorise with an old intent-',
                        [
                            'request' => $request,
                        ]
                    );
                }

                throw $e;
            }

            $email = $token->getClaim('email');

            if ($this->verifyIntent($token, $email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException('Email is not valid');
                }

                $credentials = [
                    'username' => $email,
                    'client_vendor' => $client_vendor,
                    'client_name' => $client_name,
                ];
            } else {
                throw new InvalidArgumentException('Provided intent not valid or has expired');
            }
        } else {
            $response = $this->saml_utils->parseSamlResponse($parsed_body_data);

            if (empty($response)) {
                $response = [];
            }

            $credentials = [
                'username' => $this->saml_utils->getEmailAddress($response),
                'remember' => $this->saml_utils->getSessionDurationType($response) === SessionInterface::SESSION_DURATION_LONG,
            ];

            $redirect_url = rtrim($this->saml_utils->getIssuerUrl($response), '/');
            $redirect_url .= strpos($redirect_url, '?') === false
                ? '/?prevent_redirect=1'
                : '&prevent_redirect=1';
            $payload = new MovedResource($redirect_url, false);
        }

        return new RequestProcessingResult($credentials, $payload);
    }

    private function isIntentRequest(ServerRequestInterface $request): bool
    {
        return array_key_exists('intent', $request->getParsedBody());
    }

    private function verifyIntent(Token $token, string $email): bool
    {
        if (!defined('SHEPHERD_JWT_KEY')) {
            throw new RuntimeException('JWT key was not defined.');
        }

        return $this->verifySignature($token)
            && $this->verifyIntentIssuer($token)
            && $this->verifyAudience($token)
            && $this->verifyExpiration($token)
            && $this->verifyEmail($email);
    }

    private function verifyIntentIssuer(Token $token)
    {
        $issuer = $token->getClaim('iss');

        return in_array($issuer, [self::INTENT_ISSUER_SHEPHERD]);
    }

    private function verifySignature(Token $token): bool
    {
        if (!defined('SHEPHERD_JWT_KEY')) {
            throw new RuntimeException('JWT key was not defined.');
        }

        return $token->verify($this->jwt_signer, base64_encode(SHEPHERD_JWT_KEY));
    }

    private function verifyAudience(Token $token): bool
    {
        return $token->getClaim('aud') === self::INTENT_AUDIENCE_ACTIVECOLLAB;
    }

    private function verifyExpiration(Token $token): bool
    {
        return (int) $token->getClaim('exp') >= $this->current_timestamp->getCurrentTimestamp();
    }

    private function verifyEmail(string $email): bool
    {
        return !empty($email);
    }
}
