<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitmagentovb
{
    const CURL_TIMEOUT_SEC = 10;
    const APIKEY_HASH_LIFETIME = 20; // in seconds, see storeHashLifetime()
    
    
    /**
     * Validate hash
     *
     * @param string $hash
     * @param string $secureSalt
     * @return boolean
     */
    static public function isValidApikeyHash($hash, $secureSalt)
    {
        global $vbulletin;
        $hash = rawurldecode($hash);
        
        // $randomSalt - random
        // $key = md5( md5($apikey.$secureSalt) . $randomSalt )
        // $hash = "$key:$randomSalt"
        
        if (!$hash OR !$secureSalt OR !$vbulletin->options['aitmagentovb_apikey']) return false;
        
        $hashArr = explode(':', $hash);
        if (count($hashArr) == 2)
        {
            $thisKey    = $hashArr[0];
            $randomSalt = $hashArr[1];
            $apiKey     = $vbulletin->options['aitmagentovb_apikey'];
            $realKey    = md5( md5($apiKey . $secureSalt) . $randomSalt);
            if ($realKey == $thisKey)
            {
                $sql = '
                    SELECT 
                        *,
                        IF(`validthru` < NOW(), 1, 0) AS is_expired
                    FROM `'.TABLE_PREFIX.'aitmagentovb_hash`
                    WHERE `hash` = "'.$hash.'"
                '; 
                // it's safe enough for non-escape usage of $hash here
                $hashRow = $vbulletin->db->query_first($sql);
                if ($hashRow AND $hashRow['hashid']) 
                {
                    $vbulletin->db->query_write('
                        DELETE FROM `'.TABLE_PREFIX.'aitmagentovb_hash` 
                        WHERE `hashid`='.$hashRow['hashid']
                    );
                    
                    if (!$hashRow['is_expired'] AND 'mage' == $hashRow['created_by'])
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    static public function outputJsonResult($result, $die = false)
    {
        $s = Ait_Zend_Json::encode($result);
        
        header('Content-Type: text/json');
        header('Content-Length: ' . strlen($s));
        echo $s;
        if ($die)
        {
            exit;
        }
    }
    
    static public function outputBlankGif()
    {
        // transparent GIF 1x1 pixel // real binary data is needed for login/logout images
        $sBlankGifBase64 = 'R0lGODlhAQABAID/AP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        $sBlankGif = base64_decode($sBlankGifBase64);
        
        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen($sBlankGif));
        echo $sBlankGif;
        exit;
    }
    
    /**
     * Generate a password salt
     *
     * @return string
     */
    static public function generateVbPasswordSalt()
    {
		$hashSalt = '';
		for ($i = 0; $i < 3; $i++)
		{
			$hashSalt .= chr(rand(0x41, 0x5A)); // A..Z
		}
		
		return $hashSalt;
    }
    
    static public function generateApiHash($urlencode, $secureSalt)
    {
        global $vbulletin;
        
        $apiKey = $vbulletin->options['aitmagentovb_apikey'];
        $randomSalt = self::generateVbPasswordSalt();
        
        $hash = md5( md5($apiKey.$secureSalt) . $randomSalt ) . ':' . $randomSalt;
        
        // store hash and its lifetime to vbulletin database
        self::storeHashLifetime($hash);
        
        if ($urlencode)
        {
            $hash = rawurlencode($hash);
        }
        
        return $hash;
    }
    
    static public function mergeRequestVars($aVars, $urlencode = true, $varskey = '')
    {
        if (!$aVars) return '';
        
        $sReq = '';
        foreach ($aVars as $key => $val) 
        {
            if (is_array($val))
            {
                $key = $varskey ? $varskey.'['.$key.']' : $key;
                $sReq .= '&'.self::mergeRequestVars($val, $urlencode, $key);
            }
            else 
            {
                $val = ($urlencode ? rawurlencode($val) : $val);
                if ($varskey)
                {
                    $sReq .= '&'.$varskey.'['.$key.']='.$val;
                }
                else 
                {
                    $sReq .= '&'.$key.'='.$val;
                }
            }
        }
        $sReq = substr($sReq, 1); 
        return $sReq;
    }
    
    static public function getProxyUrl($aVars, $mageLink, $secureSalt)
    {
        global $vbulletin;
        
        $_allowed_proxy_actions = array(
            'login', 
            'logout',
            'unlink', /** @since 0.3.0 */
        );
        
        if (!isset($aVars['do']) OR !in_array($aVars['do'], $_allowed_proxy_actions))
        {
            return '';
        }
        
        if (is_null($mageLink))
        {
            $mageLink = $vbulletin->options['aitmagentovb_url'];
        }
        $mageLink = rtrim($mageLink, '/') . '/';
        
        $url = $mageLink . 'aitvbulletin/proxy/' . $aVars['do'] . '/?';
        unset($aVars['do']);
        
        $aVars['h'] = self::generateApiHash(false, $secureSalt);
        
        $url .= self::mergeRequestVars($aVars, true);
        
        return $url;
    }
    
    static public function storeHashLifetime($hash)
    {
        global $vbulletin;
        
        if (!$hash) return false;
        
        $validThru = TIMENOW + self::APIKEY_HASH_LIFETIME;
        
        $vbulletin->db->query_write('
            INSERT INTO `'.TABLE_PREFIX.'aitmagentovb_hash`
            (`hash`, `validthru`, `created_by`) 
            VALUES 
            ("'.$vbulletin->db->escape_string($hash).'", "'.date('Y-m-d H:i:s', $validThru).'", "vbul")
        ');
    }
    
    static public function cleanHashes()
    {
        global $vbulletin;
        
        $vbulletin->db->query_write('
            DELETE FROM `'.TABLE_PREFIX.'aitmagentovb_hash` 
            WHERE LENGTH(`hash`) != 36
               OR `validthru` < NOW() 
               OR `created_by` NOT IN ("mage", "vbul")
        ');
    }
    
    static public function curlWrite($method, $url, $http_ver = '1.1', $headers = array(), $body = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST']);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT_SEC);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') 
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        elseif ($method == 'GET') 
        {
        	curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        if ( is_array($headers) ) 
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $result = curl_exec($ch);
        
        if (!$result) return array();
        
        // Remove 100 and 101 responses headers
        if (Ait_Zend_Http_Response::extractCode($result) == 100 ||
            Ait_Zend_Http_Response::extractCode($result) == 101) {
            $result = preg_split('/^\r?$/m', $result, 2);
            $result = trim($result[1]);
        }
        
        $oResponse = Ait_Zend_Http_Response::fromString($result);
        $sResponce = trim($oResponse->getRawBody());
        $aResponce = Ait_Zend_Json::decode($sResponce);
        
        return $aResponce;
    }
}
