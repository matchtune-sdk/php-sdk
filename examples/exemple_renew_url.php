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
** THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
** IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
** FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
** THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
** LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
** FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
** DEALINGS IN THE SOFTWARE.
*/

require_once __DIR__ . '/credentials.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MatchTune\MatchTune

$client_id = MATCHTUNE_CLIENT_ID;
$app_token = retreivetoken($client_id);
$api = new MatchTune([ "app_token"   => $app_token,
                    "app_id"      => MATCHTUNE_APP_ID,
                    "app_secret"  => MATCHTUNE_APP_SECRET]);
$version = $api->apiVersion();
echo "API Version : $version\n";

$haserror = true;
// -- login if needed
if ($app_token != null || $result = $api->apiLogin($client_id, MATCHTUNE_TOS)) {

  // -- save the token
  savetoken($client_id, $api->getCurrentToken());

  // -- get all genre & subgenre
  if ($genres = $api->genres()) {

    // -- pick a random genre
    $genre = $genres[array_rand($genres)];

    // -- create a search query
    $query = $api->makeQuery($genre, $subgenre = null, $title = null, $tags = null);

    // -- request a standard generated music
    if ($idcard = $api->generate($query)) {
      print_r($idcard);
      // -- if you already have a license for this title, you may don't have to request a new one
      // -- please check the terms of the license  (https://www.matchtune.com/license-information)
      // -- Get a license on the title (mandatory to get unwatermarked files and full quality
      if ($idcard["license"] != "None" || $license = $api->license($idcard["finalHash"])) {
        if ($idcard["license"] != "None" || $license["status"] == "approved") {
          // -- Get a new URL only available once the song licensed
          if ($url = $api->getMusicURL($idcard["finalHash"], "HIGH")) {
            $haserror = false;

            // -- use the data
            printIDCard($idcard);

            // -- use the data
            echo "Fresh url :\n";
            print_r($url);
            echo "\n";
          }
        }
      }
    }
  }
}

if ($haserror) {
  echo "Error : \n";
  print_r($api->getLastError());
  echo "\n";
}

?>
