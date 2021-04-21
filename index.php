<?php

$errormsg = "Welcome to my WebDev COVID-19 Tracer application! Hope you enjoy!";
http_response_code(200);
echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./login.html';", "</script>"; //throws js message
exit();
