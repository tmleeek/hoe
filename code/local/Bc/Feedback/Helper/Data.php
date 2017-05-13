<?php

class Bc_Feedback_Helper_Data extends Mage_Core_Helper_Abstract
{
     const RECAPTCHA_API_SERVER_HOST = "www.google.com";
    const RECAPTCHA_API_SERVER_PATH = "/recaptcha/api";
    const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";

    
    function _recaptcha_qsencode ($data) 
    {
        $req = "";
        foreach ( $data as $key => $value )
            $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        $req=substr($req,0,strlen($req)-1);
        return $req;
    }

    function _recaptcha_http_post($host, $path, $data, $port = 80) 
    {
        $req = $this->_recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
            die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
            $response .= fgets($fs, 1160); 
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
    }


    function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false)
    {
        
        if ($pubkey == null || $pubkey == '') {
            die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
        }
    
        if ($use_ssl) {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = 'http://' . self::RECAPTCHA_API_SERVER_HOST . self::RECAPTCHA_API_SERVER_PATH;
        }

        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>

        <noscript>
              <iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
              <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
              <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
        </noscript>';
    }

    function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array())
    {
        if ($privkey == null || $privkey == '') {
            die ("To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>");
        }

        if ($remoteip == null || $remoteip == '') {
            die ("For security reasons, you must pass the remote ip to reCAPTCHA");
        }
    
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            return false;
        }

        $response = $this->_recaptcha_http_post (self::RECAPTCHA_API_SERVER_HOST, self::RECAPTCHA_API_SERVER_PATH . "/verify", array (  'privatekey' => $privkey,
                                                                                                                            'remoteip' => $remoteip,
                                                                                                                            'challenge' => $challenge,
                                                                                                                            'response' => $response
                                                                                                                         ) + $extra_params
                                                );

        $answers = explode ("\n", $response [1]);

        if (trim ($answers [0]) == 'true') {
            return true;
        }
        return false;
    }
    function recaptcha_get_signup_url ($domain = null, $appname = null) 
    {
        return "http://recaptcha.net/api/getkey?" .  $this->_recaptcha_qsencode (array ('domain' => $domain, 'app' => $appname));
    }

    function _recaptcha_aes_pad($val) 
    {
        $block_size = 16;
        $numpad = $block_size - (strlen ($val) % $block_size);
        return str_pad($val, strlen ($val) + $numpad, chr($numpad));
    }
}
