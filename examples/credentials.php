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

 /*
 * Contact developers@matchtune.com to get your own credentials
 */

/**
* @def string APP_ID provided by MatchTune.
*/
define('MATCHTUNE_APP_ID', '');

/**
* @def string APP_SECRET provided by MatchTune.
* the app secret should never be embedded in an app or provided to a webapp
* Use APP_SECRET on the server side, Use generated token on client side if needed
*/
define('MATCHTUNE_APP_SECRET', '');

/**
* @def string you may assign one token per client
* use full if you plan to transfer & store the token to a client app (ex webapp iphoneapp etc ...)
* for phone & such you may use the phone UUID for instance
* if you are using the token in server side only you may use a static client id
*/
define('MATCHTUNE_CLIENT_ID', '');

/**
* @def for every account creation you should agree to the latest terms of services
* https://www.matchtune.com/privacy-policy
*/
define('MATCHTUNE_TOS', true);


?>
