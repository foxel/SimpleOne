<?php
/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
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

class Google_API_Analytics
{
    const SCOPE_URL = 'https://www.googleapis.com/auth/analytics.readonly';
    const REPORTING_URL = 'https://www.googleapis.com/analytics/v3/data/ga';
    const MANAGEMENT_URL = 'https://www.googleapis.com/analytics/v3/management';

    /** @var Google_Auth */
    protected $_auth;

    public function __construct(Google_API_Auth $auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @param int $profileId
     * @param int $daysToFetch
     * @param string|string[] $pathFilter
     * @throws FException
     * @return array
     */
    public function getMostVisitedPagesStats($profileId, $pathFilter = null, $daysToFetch = 2)
    {
        $query = array(
            'ids'        => 'ga:'.$profileId,
            'start-date' => date('Y-m-d', time() - 3600*24*$daysToFetch),
            'end-date'   => date('Y-m-d'),
            'metrics'    => 'ga:pageviews,ga:visitors,ga:visits',
            'dimensions' => 'ga:pagePath',
            'sort'       => '-ga:pageviews,-ga:visitors,-ga:visits',
            'filters'    => 'ga:visitors>0',
        );

        if ($pathFilter) {
            $pathFilter = '('.implode('|', array_map('preg_quote', (array) $pathFilter)).')';
            $query['filters'].= ';ga:pagePath=~^/?'.preg_quote($pathFilter);
        }

        $url = self::REPORTING_URL.'?'.http_build_query($query);

        $response = Google_Misc::makeRequest($url, array(), $this->_auth->getAuthHeaders());

        if ($decoded = json_decode($response, true)) {
            return $this->_parseStatGrid($decoded);
        }

        throw new FException('Error loading Stats: '.$response);
    }

    /**
     * @param array $json
     * @return array
     */
    protected function _parseStatGrid(array $json)
    {
        $cols = (array) $json['columnHeaders'];
        $rows = (array) $json['rows'];

        $grid = array();
        foreach ($rows as $row) {
            $gridRow = array();
            foreach ($row as $key => $value) {
                $col = $cols[$key];
                $gridRow[$col['name']] = $value;
            }
            $grid[] = $gridRow;
        }

        return $grid;
    }


    /**
     * @return array[]
     * @throws FException
     */
    public function getAccounts()
    {
        $url = self::MANAGEMENT_URL.'/accounts';

        $response = Google_Misc::makeRequest($url, array(), $this->_auth->getAuthHeaders());

        if ($decoded = json_decode($response, true)) {
            return $decoded['items'];
        }

        throw new FException('Error loading Accounts: '.$response);
    }

    /**
     * @param int $accountId
     * @return array[]
     * @throws FException
     */
    public function getWebProperties($accountId)
    {
        $url = sprintf(self::MANAGEMENT_URL.'/accounts/%d/webproperties', $accountId);

        $response = Google_Misc::makeRequest($url, array(), $this->_auth->getAuthHeaders());

        if ($decoded = json_decode($response, true)) {
            return $decoded['items'];
        }

        throw new FException('Error loading WebProperties: '.$response);
    }

    /**
     * @param string $webPropertyId
     * @param int|null $accountId
     * @return array[]
     * @throws FException
     */
    public function getProfiles($webPropertyId, $accountId = null)
    {
        if ($accountId === null) {
            if (preg_match('#UA-(\d+)-\d+#', $webPropertyId, $matches)) {
                $accountId = (int) $matches[1];
            } else {
                throw new FException('Web Property ID is invalid: '.$webPropertyId);
            }
        }

        $url = sprintf(self::MANAGEMENT_URL.'/accounts/%d/webproperties/%s/profiles', $accountId, $webPropertyId);

        $response = Google_Misc::makeRequest($url, array(), $this->_auth->getAuthHeaders());

        if ($decoded = json_decode($response, true)) {
            return $decoded['items'];
        }

        throw new FException('Error loading Profiles: '.$response);
    }

    /**
     * @param string $webPropertyId
     * @return int
     */
    public function getFistProfileId($webPropertyId)
    {
        list($profile) = $this->getProfiles($webPropertyId);
        return $profile['id'];
    }
}
