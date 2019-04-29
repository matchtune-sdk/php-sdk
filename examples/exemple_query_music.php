<?php

/*
** Copyright 2019 Muzeek Inc.
**
** You are hereby granted a non-exclusive, worldwide, royalty-free license to
** use, copy, modify, and distribute this software in source code or binary
** form for use in connection with the web services and APIs provided by
** Muzeek.
**
** As with any software that integrates with the Muzeek platform, your use
** of this software is subject to the Muzeek terms of services and
** Policies [https://app.muzeek.co/terms-of-service]. This copyright notice
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

require 'credentials.php';
require 'helpers.php';
require 'muzeek.php';

$client_id = MUZEEK_CLIENT_ID;
$app_token = retreivetoken($client_id);
$api = new Muzeek(["app_token" => $app_token]);
$version = $api->apiVersion();
echo "API Version : $version\n";

$haserror = true;
// -- login if needed
if ($app_token != null || $result = $api->apiLogin($client_id, MUZEEK_APP_ID, MUZEEK_APP_SECRET, MUZEEK_TOS)) {

  // -- save the token
  savetoken($client_id, $api->getCurrentToken());

  // -- get all genre & subgenre
  if ($genres = $api->genres()) {

    // -- pick a random genre
    $genre = array_rand($genres);

    // -- create a search query
    $query = $api->makeQuery($genre, $subgenre = null, $title = null, $tags = null);

    // -- request a standard generated music
    if ($idcard = $api->generate($query)) {
      $haserror = false;

      // -- use the data
      printIDCard($idcard);
    }
  }
}

if ($haserror) {
  echo "Error : \n";
  print_r($api->getLastError());
  echo "\n";
}

?>
