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

/**
* format and print an id card
*
* @return null
*/

function printIDCard($idcard) {
  $metadata = $idcard["metadata"];
  $coverurl = $metadata["cover"]["medium"];
  $musicurl = $idcard["urls"]["LOW"]; // Warning this url expires in 3 minutes
  $prettyname = sprintf("%s %d (%s, %s)", $metadata["title"], $metadata["recordId"], $metadata["genre"], $metadata["subgenre"]);
  $descriptor = sprintf("Duration:%ds, Tempo:%dBPM, Pitch:%s, Signature:%d/%d", round($metadata["duration"] / 1000), $metadata["tempo"], $metadata["pitch"], $metadata["numerator"], $metadata["denominator"]);

  echo "Display name : $prettyname\n";
  echo "Description  : $descriptor\n";
  echo "Cover URL    : $coverurl\n";
  echo "Music URL    : $musicurl\n";
  echo "\n";

  return null;
}


/**
* save token to file (note this should be a database, this token should be encrypted)
*
* @return null
*/

function savetoken($clientid, $token) {
  file_put_contents(".matchtunetoken_".$clientid, json_encode($token));

  return null;
}

/**
* retreive token from file (note this should be a database
*
* @return token
*/

function retreivetoken($clientid) {
  if (is_file(".matchtunetoken_".$clientid)) {
    $token = json_decode(file_get_contents(".matchtunetoken_".$clientid), true);
    if (time() < $token["expiration"]) {
      return $token;
    }
  }
  
  return null;
}

?>
