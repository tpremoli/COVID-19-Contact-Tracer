<?php
//default curl connection settings
if (($handle = curl_init()) === false) {
    echo "ERROR : " . curl_error($handle);
} else {
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);
}
