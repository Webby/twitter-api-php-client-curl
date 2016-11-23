<?php
/**
* \author Anthony Desvernois
* \brief PHP simple Client for Twitter API, following OAuth1.0a protocol with https://dev.twitter.com/ access credential
*
*/
require_once('config.php');

class TwitterClient {

    /**
     * \fn sign() compute the HMAC-SHA1 signature of the request
     * \param uri uri to request
     * \param method HTTP method used
     * \param nonce unique number to use for the request
     * \param parameters array of OAuth parameters
     * \param data array of data used by the request 
     * \return HMAC-SHA1 signature
     *
     */
      private function sign($uri, $method, $nonce, $parameters, $data) {
          $base = $method.'&'.rawurlencode($uri).'&';
          $parameters = array_merge($parameters, $data);
          ksort($parameters);
          array_map('rawurlencode', $parameters);
          array_map('rawurlencode', array_keys($parameters));
          $pstring = '';
          foreach ($parameters as $key => $value)
              $pstring .= sprintf("%s=%s&", $key, $value);
          $pstring = substr($pstring, 0, strlen($pstring) - 1);
          $base .= rawurlencode($pstring);
          $signingKey = rawurlencode(CONSUMER_SECRET).'&'.rawurlencode(ACCESS_SECRET);
          return base64_encode(hash_hmac('sha1', $base, $signingKey, true));
      }
      
      /**
      * \fn getResources() request json data from the API provider
      * \param uri uri to request
      * \param method optional parameter specifying the HTTP method to use
      * \param data optional parameter specifying the data to send - must be an array
      * \return array of results
      *
      */
      private function getResources($uri, $method = 'GET', $data = null) {
          if ($data === null)
              $data = array();
          $ch = curl_init();
          $nonce = md5(mt_rand(1, 10000000)); // \todo: to improve
          //$nonce = 'f7cfa955b4ba8637862a43310f5313f4';
          $tstamp = time();
          //$tstamp = 1479936192;
          $parameters = array("oauth_consumer_key" => CONSUMER_KEY,
          "oauth_nonce" => $nonce,
          "oauth_signature_method" => "HMAC-SHA1",
          "oauth_timestamp" => $tstamp,
          "oauth_token" => ACCESS_TOKEN,
          "oauth_version" => "1.0");
          $signature = $this->sign($uri, $method, $nonce, $parameters, $data);
          $options = array(CURLOPT_URL => $uri,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_HTTPHEADER => array(sprintf('Authorization: OAuth oauth_consumer_key="%s", oauth_nonce="%s", oauth_signature="%s", oauth_signature_method="HMAC-SHA1", oauth_timestamp="%d", oauth_token="%s", oauth_version="1.0"',
          CONSUMER_KEY, $nonce, rawurlencode($signature), $tstamp, ACCESS_TOKEN)));
          curl_setopt_array($ch, $options);
          if ($method == 'POST')
              curl_setopt($ch, CURLOPT_POST, true);
          if ($data !== null && $method == 'POST')
              curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

          return json_decode(curl_exec($ch), true);
      }

      /**
       * \fn sendTweet send a simple text-only tweet
       * \param msg text to use
       * \return array with the API response
       *
       */
      public function sendTweet($msg) {
          return $this->getResources(HOST.'1.1/statuses/update.json', 'POST', array('status' => $msg));
      }

      /**
       * \fn getAccountSettings() give the current user settings
       * \return array with the API response
       *
       */
      public function getAccountSettings() {
          return $this->getResources(HOST.'1.1/account/settings.json', 'GET');
      }
}

?>