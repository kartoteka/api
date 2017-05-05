<?php

/**
 * Created by Kartoteka team.
 * User: ek
 * Date: 02.05.2017
 */

namespace Kartoteka;

class Api
{
    private $_url = 'https://api.kartoteka.by/';
    private $_errors = array();
    private $_token = '';

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param array $errors
     */
    public function addErrors($errors)
    {
        if (is_array($errors)) {
            $this->_errors = $this->_errors + $errors;
        } else {
            $this->_errors[] = $errors;
        }
    }

    public function hasErrors()
    {
        if (empty($this->_errors)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->_token = $token;
    }

    public function __construct($accessToken)
    {
        $this->setToken($accessToken);

        return $this;
    }


    /**
     * @param string $queryString
     * @param array  $params
     *
     * @return bool|array
     */
    private function sendRequest($queryString = '', $params = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, false); // заголовки
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
        ));

        curl_setopt($curl, CURLOPT_URL, $this->_url . $queryString);
        $json = curl_exec($curl);
//        $info = curl_getinfo($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode !== 200) {
            throw new Exception('Server error', 500);
        }

        $data = json_decode($json, true);

        if (empty($data['success'])) {
            if (!empty($data['errors'])) {
                $this->addErrors($data['errors']);
            }

            return false;
        }

        return $data['data'];
    }

    private function getData($row, $params = array())
    {
        $queryString = $row;
        $queryString .= '?' . http_build_query($params + array('access-token' => $this->getToken()));

        try {
            $data = $this->sendRequest($queryString);
        } catch (Exception $e) {
            $this->addErrors('Curl request is fail.');

            return false;
        }

        return $data;
    }


    /**
     * @param array $unp
     *
     * @return array|bool
     */
    public function getEgrInfo(array $unp)
    {
        return $this->getData('egr/' . implode(',', $unp));
    }

    public function getCourtWrit($unp)
    {
        return $this->getData('court/writ/' . $unp);
    }

    public function getCourtSessions($unp)
    {
        return $this->getData('court/sessions/' . $unp);
    }

    public function getChecks($unp)
    {
        return $this->getData('checks/' . $unp);
    }

    public function getDebtors($unp)
    {
        return $this->getData('debtors/' . $unp);
    }
}