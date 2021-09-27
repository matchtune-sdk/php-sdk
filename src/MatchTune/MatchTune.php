<?php

/*
** Copyright 2021 MatchTune Inc.
**
** You are hereby granted a non-exclusive, worldwide, royalty-free license to
** use, copy, modify, and distribute this software in source code or binary
** form for use in connection with the web services and APIs provided by
** MatchTune.
**
** As with any software that integrates with the MatchTune platform, your use
** of this software is subject to the MatchTune terms of services and
** Policies [https://www.matchtune.com/privacy-policy]. This copyright notice
** shall be included in all copies or substantial portions of the software.
**
** THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
** IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
** FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
** THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
** LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
** FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
** DEALINGS IN THE SOFTWARE.
*/
namespace MatchTune;

/**
* Class MatchTune
*
* @package MatchTune
*/
class MatchTune
{
  /**
  * @const string Version number of the MatchTune PHP SDK.
  */
  const VERSION = '1.0.0';

  /**
  * @const string Default endpoint of MatchTune PHP SDK.
  */
  const APP_ENDPOINT_DEFAULT = 'https://api.matchtune.com';

  /**
  * @const string The name of the environment variable that contains the app ID.
  */
  const APP_ID_ENV_NAME = 'MATCHTUNE_APP_ID';

  /**
  * @const string The name of the environment variable that contains the app secret.
  */
  const APP_SECRET_ENV_NAME = 'MATCHTUNE_APP_SECRET';

  /**
  * @const string The name of the environment variable that contains the app secret.
  */
  const APP_TOKEN_ENV_NAME = 'MATCHTUNE_APP_TOKEN';

  /**
  * @var array The configuration store.
  */
  protected $config = [];

  /**
  * @var array The last error.
  */
  protected $lasterror = null;

  /**
  * Instantiates a new MatchTune super-class object.
  *
  * @param array $config
  *   You should set app_id / app_secret / app_token in the contructor or in the environment
  *   using respectively APP_ID_ENV_NAME / APP_SECRET_ENV_NAME / APP_TOKEN_ENV_NAME
  *
  */
  public function __construct(array $config = [])
  {
    $config = array_merge([
      'app_endpoint'  => static::APP_ENDPOINT_DEFAULT,
      'app_id'        => getenv(static::APP_ID_ENV_NAME),
      'app_secret'    => getenv(static::APP_SECRET_ENV_NAME),
      'app_token'     => getenv(static::APP_TOKEN_ENV_NAME)
    ], $config);

    $this->config = $config;
  }

  /**
  * Calls the api
  *
  * @param string $target
  *   The url target
  *
  * @param string $method
  *   HTTP Method : GET/POST/PUT/DELETE ...
  *
  * @param array $data
  *   (optional) Argument to send to the endpoint
  *
  * @return array $result
  *   returns server result or null if failed
  */
  protected function callAPI($target, $method, $data = false)
  {
    $url                = $this->config['app_endpoint'] . '/' . $target;
    $curl               = curl_init();
    //curl_setopt($curl, CURLOPT_VERBOSE, true);
    $this->lasterror    = null;

    switch ($method)
    {
      case 'POST':
      curl_setopt($curl, CURLOPT_POST, 1);
      if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      }
      break;
      case 'PUT':
      curl_setopt($curl, CURLOPT_PUT, 1);
      break;
      default:
      if ($data) {
        $url = sprintf('%s?%s', $url, http_build_query($data));
      }
    }

    // Optional Authentication:
    $headers            = [];
    $headers[]          = 'Content-Type: application/json';
    $headers[]          = 'User-Agent: MatchTune-PHP-SDK/' . static::VERSION . ' (' . php_uname() . ')';

    if ($this->config['app_token'] != null) {
      $headers[]        = 'Authorization: Bearer ' . $this->config['app_token']['value'];
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result             = curl_exec($curl);
    $status             = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $data               = json_decode($result, true);

    if ($status == '200') {
      $this->lasterror  = null;
      return $data['data']['attributes'];
    }

    if (isset($data['errors'])) {
      $this->lasterror = array_pop($data['errors']);
    } else {
      $this->lasterror = array_pop($data);
    }

    return null;
  }

  /**
  * Returns current JWT token for reuse
  *
  * @return array $token
  *   returns JWT Token
  */
  public function getCurrentToken()
  {
    return $this->config['app_token'];
  }

  /**
  * Returns last server error
  *
  * @return string $error
  *   Returns last server error
  */
  public function getLastError()
  {
    return $this->lasterror;
  }

  /**
  * Retreive api version
  *
  * @return string $version
  *   Returns API Version
  */
  public function apiVersion()
  {
    $result = $this->callAPI('version', 'GET');
    return $result['version'];
  }

  /**
  * Login to API using apikey
  *
  * @param string $client_id
  *   Identification for the client ex device UUID, account identification, email etc ...
  *
  * @param boolean $tos
  *   Acceptation of the terms of services as listed https://www.matchtune.com/privacy-policy
  *
  * @return boolean
  *   True if successfull, False otherwise
  */
  public function apiLogin($client_id, $tos)
  {
    $app_id                   = $this->config['app_id'];
    $app_secret               = $this->config['app_secret'];
    $unixtime                 = time();
    $payload                  = $unixtime.$client_id.$app_id;
    $signature                = base64_encode(hash_hmac('sha256', $payload, $app_secret, TRUE));

    $attributes               = [];
    $attributes['UNIXTIME']   = $unixtime;
    $attributes['UUID']       = $client_id;
    $attributes['APPId']      = $app_id;
    $attributes['tos']        = $tos;
    $attributes['signature']  = $signature;

    $data                     = ['data' => ['type' => 'tokens', 'attributes' => $attributes]];

    $result = $this->callAPI('tokens', 'POST', $data);
    if ($result != null) {
      $this->config['app_token'] = $result;
      return true;
    }
    return false;
  }

  /**
  * Login to API using login and password
  *
  * @param string $email
  *   If no API key is available, API can be use with standard email/password method
  *
  * @param string $password
  *   Password of the user
  *
  * @return boolean
  * True if successfull, False otherwise
  */
  public function standardLogin($email, $password)
  {
    $data = ['data' => ['type' => 'tokens', 'attributes' => ['email' => $email, 'password' => $password]]];

    $result = $this->callAPI('tokens', 'POST', $data);
    if ($result != null) {
      $this->config['app_token'] = $result;
      return true;
    }
    return false;
  }

  /**
  * Retreive all available genre and subgenre
  *
  * @return array of genres
  */
  public function genres()
  {
    $data = [];

    $result = $this->callAPI('classifiers', 'GET', $data);
    if ($result != null) {
      return $result["genres"];
    }
    return false;
  }

  /**
  * Assemble a search query
  *
  * @param string $genre
  *   Genre from the list of genre (possible to send an array of genre)
  *
  * @param string $title
  *   Title of a known matrix (possible to send an array of title)
  *
  * @param array $tags
  *   Query to search using meaningfull tag words
  *
  * @return array formated dictionnary
  */
  public function makeQuery($genre = null, $title = null, $tags = null)
  {
    $query = [];
    if ($genre != null) {
      $query['genres']            = $genre;
    }
    if ($title != null) {
      $query['title']             = $title;
    }
    if ($tags != null) {
      $query['tags']              = $tags;
    }
    return $query;
  }

  /**
  * Assemble a feature query
  *
  * @param int $timecode
  *   Set a climax at this time code in milliseconds
  *
  * @param boolean $withRiser
  *   Enhence the climax using a riser sound effect before the climax
  *
  * @param boolean $withDrop
  *   Enhence the climax using a drop sound effect after the climax
  *
  *
  * @return array formated dictionnary
  */
  public function makeClimaxFeature($timecode, $withRiser = true, $withDrop = true)
  {
    $feature = [];
    $feature['cursor']            = $timecode;
    $feature['cutAfterPoint']     = true;
    $feature['followingPartType'] = 'climax';
    $feature['isFinal']           = false;
    $feature['addonsTypes']       = [];
    if ($withRiser) {
      $feature['addonsTypes'][]   = 'riser';
    }
    if ($withDrop) {
      $feature['addonsTypes'][]   = 'drop';
    }

    return $feature;
  }

  /**
  * Filter ai-generated musics
  *
  * @param array $card
  *   id card as received from generation
  *
  * @return array filtered output
  */
  private function _filterIDCard($card) {
    $output = [];
    $output['license']              = $card[0]['license'];
    $output['finalHash']            = $card[0]['finalHash'];
    $output['metadata']             = $card[0]['metadata'];
    $output['metadata']['duration'] = $card[0]['totalDuration'];
    $output['urls']                 = $card[0]['urls'];

    unset($output['metadata']['characteristics']);
    return $output;
  }

  /**
  * Retreive ai-generated musics
  *
  * @param array $query
  *   Use the result from makeQuery()
  *
  * @return array idcard
  */
  public function generate($query = [])
  {
    $data = ['data' => ['type' => 'search', 'attributes' => $query]];

    $result = $this->callAPI('search', 'POST', $data);
    if ($result != null) {
      return $this->_filterIDCard($result);
    }
    return false;
  }

  /**
  * Customize ai-generated musics
  *
  * @param array $query
  *   Use the result from makeQuery()
  *
  * @param array $features
  *   Use a list of results from makeClimaxFeature()
  *
  * @return array
  *   Returns a new id card
  */
  public function customize($duration, $query = [], $features = [])
  {
    $attributes               = $query;
    $attributes['duration']   = $duration;
    $attributes['syncPoints'] = $features;

    $data = ['data' => ['type' => 'search', 'attributes' => $attributes]];
    $result = $this->callAPI('search', 'POST', $data);
    if ($result != null) {
      return $this->_filterIDCard($result);
    }
    return false;
  }

  /**
  * Customize ai-generated musics
  *
  * @param string $finalHash
  *   Use the finalHash of an existing id card
  *
  * @param int $duration
  *   Choose a new duration to change lenght
  *
  * @param array $features
  *   Use a list of results from makeClimaxFeature()
  *
  * @return array
  *   Returns a new id card
  */
  public function modify($finalHash, $duration, $features = [])
  {
    $attributes                     = [];
    $attributes['finalHash']        = $finalHash;
    $attributes['duration']         = $duration;
    $attributes['syncPoints']       = $features;

    $data = ['data' => ['type' => 'customize', 'attributes' => $attributes]];

    $result = $this->callAPI('search', 'POST', $data);
    if ($result != null) {
      return $this->_filterIDCard($result);
    }
    return false;
  }

  /**
  * License one or many ai-generated musics
  * see https://www.matchtune.com/license-information for more details
  * applyCharges automatically purchase the track, api user can automatically set it to true
  * users that logs in with a regular account (not an api key) should first retreive a quote using applyCharges = false
  * api users can only purchase premium licenses
  *
  * @param string $finalHash
  *   Use the finalHash of an existing id card
  *
  * @param string $license
  *   License type standard or premium, api user may only select premium license
  *
  * @param boolean $applyCharges
  *   API users may directly set $applyCharges to True
  *   Standard users should request a quote using $applyCharges = False then confirm the buy using $applyCharges = True
  *
  * @return array
  *   Price or credit applied (usefull when regular login, api user are charged after use based on volume)
  */
  public function license($finalHash, $license = 'premium', $applyCharges = TRUE)
  {
    $attributes                     = [];
    $attributes['finalHash']        = is_array($finalHash) ? $finalHash : [$finalHash];
    $attributes['license']          = $license;
    $attributes['applyCharges']     = $applyCharges;

    $data = ['data' => ['type' => 'purchase', 'attributes' => $attributes]];

    $result = $this->callAPI('musics/purchase', 'POST', $data);
    if ($result != null) {
      return $result;
    }
    return false;
  }

  /**
  * get an url for a known music
  *
  * @param string $finalHash
  *   Use the finalHash of an existing id card
  *
  * @param string $quality
  *   Available qualities are protected, low, high, lossless
  *   If the music is not licensed only the protected version is available.
  *   Note : protected means low quality with watermark
  *
  * @return string
  *   Returns a fresh temporary URL to use for preview or download
  *
  */
  public function getMusicURL($finalHash, $quality)
  {
    $data = null;

    $result = $this->callAPI('musics/'.$finalHash.'/download?quality='.$quality.'&forwarding=false', 'GET', $data);
    if ($result != null) {
      return $result;
    }
    return false;
  }
}
?>
