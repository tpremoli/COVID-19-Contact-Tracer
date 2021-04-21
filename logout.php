<?php
//logout procedure
Session_start();
Session_destroy();
http_response_code(200);
header("Location: login.html");
