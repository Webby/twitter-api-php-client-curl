<?php
require_once('TwitterClient.class.php');
$api = new TwitterClient();
// POST
try {
    print_r($api->sendTweet('Test !'));
} catch (Exception $e) {
    print_r($e);
    }

// GET
try {
    print_r($api->getAccountSettings());
} catch (Exception $e) {
    print_r($e);
}

?>
