<?php


class Google_Misc
{
    protected static $_curlParams = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => 0,
        CURLOPT_FAILONERROR    => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_VERBOSE        => false,
    );

    /**
     * @param string $url
     * @param string|array $body
     * @param array $headers
     * @return string
     * @throws FException
     */
    public static function makeRequest($url, $body, array $headers = array())
    {
        $ch = curl_init();
        curl_setopt_array($ch, self::$_curlParams);
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($body) {
            if (is_array($body)) {
                $body = http_build_query($body);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        if ($headers && is_array($headers)) {
            $parsed = array();
            foreach ($headers as $k => $v) {
                $parsed[] = "$k: $v";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $parsed);
        }

        $respData = curl_exec($ch);

        $respHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorNum = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($curlErrorNum != CURLE_OK) {
            throw new FException("HTTP Error: ({$respHttpCode}) {$curlError}");
        }

        return $respData;
    }
}
