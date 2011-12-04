<?php
//require the main library base
require("api_base.class.php");

//determine functions to load
API_BASE::determineRequestMethod();

//construct the onbject
$api = new API_FUNCTIONS();

//execute the API call
$api->execute();
?>