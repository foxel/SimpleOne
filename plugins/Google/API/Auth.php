<?php
/**
 * Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
 *
 * This file is part of QuickFox SimpleOne.
 *
 * SimpleOne is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleOne is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
 */

class Google_API_Auth
{
    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';
    const ASSERTION_TYPE = 'http://oauth.net/grant_type/jwt/1.0/bearer';
    const MAX_TOKEN_LIFETIME_SECS = 3600;

    protected $_serviceAccountName;
    protected $_scopes;
    protected $_privateKeyPath;
    protected $_privateKey;
    protected $_privateKeyPassword;
    protected $_token;

    /**
     * @param string $accountName
     * @param string|array $scopes array List of scopes
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     * @throws FException
     */
    public function __construct($accountName, $scopes, $privateKeyPath, $privateKeyPassword = 'notasecret')
    {
        $this->_serviceAccountName = $accountName;
        $this->_scopes = is_string($scopes) ? $scopes : implode(' ', $scopes);
        $this->_privateKeyPath = $privateKeyPath;
        $this->_privateKeyPassword = $privateKeyPassword;

        if (!function_exists('openssl_x509_read')) {
            throw new FException('openssl PHP extension required');
        }

        if (!is_readable($privateKeyPath)) {
            throw new FException('p12 file not found at '.$privateKeyPath);
        }

        // This throws on error
        $certs = array();
        if (!openssl_pkcs12_read(file_get_contents($privateKeyPath), $certs, $privateKeyPassword)) {
            throw new FException('Unable to parse the p12 file. OpenSSL error: '.openssl_error_string());
        }

        if (empty($certs['pkey'])) {
            throw new FException("No private key found in p12 file.");
        }
        $this->_privateKey = openssl_pkey_get_private($certs['pkey']);
        if (!$this->_privateKey) {
            throw new FException("Unable to load private key in ");
        }
    }

    /**
     * @param string|array $token
     * @return Google_API_Auth
     * @throws FException
     */
    public function setAccessToken($token)
    {
        if (!is_array($token)) {
            $token = json_decode($token, true);
            if ($token == null) {
                throw new FException('Could not decode the token from json');
            }
        }

        if (!isset($token['access_token'])) {
            throw new FException("Invalid token format");
        }

        $this->_token = $token;

        return $this;
    }

    /**
     * @return string
     */
    protected function _generateAssertion()
    {
        $now = time();

        $jwtParams = array(
            'aud'   => self::OAUTH2_TOKEN_URI,
            'scope' => $this->_scopes,
            'iat'   => $now,
            'exp'   => $now + self::MAX_TOKEN_LIFETIME_SECS,
            'iss'   => $this->_serviceAccountName,
        );

//        if ($this->prn !== false) {
//            $jwtParams['prn'] = $this->prn;
//        }

        return $this->_makeSignedJwt($jwtParams);
    }

    /**
     * Creates a signed JWT.
     * @param array $payload
     * @return string The signed JWT.
     */
    protected function _makeSignedJwt(array $payload)
    {
        $header = array('typ' => 'JWT', 'alg' => 'RS256');

        $segments = array(
            $this->_urlSafeB64Encode(json_encode($header)),
            $this->_urlSafeB64Encode(json_encode($payload)),
        );

        $signingInput = implode('.', $segments);
        $signature = $this->_signJWTRequest($signingInput);
        $segments[] = $this->_urlSafeB64Encode($signature);

        return implode('.', $segments);
    }

    /**
     * @param string $data
     * @return string
     * @throws FException
     */
    protected function _signJWTRequest($data)
    {
        if (!openssl_sign($data, $signature, $this->_privateKey, 'sha256')) {
            throw new FException('Unable to sign data');
        }
        return $signature;
    }

    public function __destruct()
    {
        if ($this->_privateKey) {
            openssl_pkey_free($this->_privateKey);
        }
    }

    /**
     * @param string $data
     * @return string
     */
    protected function _urlSafeB64Encode($data)
    {
        $b64 = base64_encode($data);
        $b64 = str_replace(array('+', '/', '\r', '\n', '='),
            array('-', '_'),
            $b64);
        return $b64;
    }

    /**
     * @return array
     */
    public function getAuthHeaders()
    {
        if ($this->isAccessTokenExpired()) {
            $this->refreshToken();
        }

        return array('Authorization' => 'Bearer '.$this->_token['access_token']);
    }

    /**
     * @return bool
     */
    public function isAccessTokenExpired()
    {
        if (null == $this->_token) {
            return true;
        }

        // If the token is set to expire in the next 30 seconds.
        $expired = ($this->_token['created']
            + ($this->_token['expires_in'] - 30)) < time();

        return $expired;
    }

    public function refreshToken()
    {
        $this->_refreshTokenRequest(array(
            'grant_type' => 'assertion',
            'assertion_type' => self::ASSERTION_TYPE,
            'assertion' => $this->_generateAssertion(),
        ));

        return $this;
    }

    protected function _refreshTokenRequest($params)
    {
        $response = Google_Misc::makeRequest(self::OAUTH2_TOKEN_URI, $params);

        $token = json_decode($response, true);
        if ($token == null) {
            throw new FException("Could not json decode the access token");
        }

        if (!isset($token['access_token']) || !isset($token['expires_in'])) {
            throw new FException('Invalid token format');
        }

        $this->_token['access_token'] = $token['access_token'];
        $this->_token['expires_in']   = $token['expires_in'];
        $this->_token['created']      = time();
    }
}
