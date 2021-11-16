<?php

function get_aa_data($json, $apiClient) {
    $cache_path = './cache/';
    $is_cached = false;

    // check cache dir for a filename that matches the hashed JSON
    $json_hash = hash('crc32b', $json);

    $cache_files = scandir($cache_path);

    // if cache dir doesn't exist, create it.
    if ($cache_files == false) {
        mkdir($cache_path);
    } elseif (in_array($json_hash, $cache_files)) {
        return file_get_contents($cache_path . $json_hash);
    }

    $data = $apiClient->requestEntity($json);

    file_put_contents($cache_path . $json_hash, $data);

    return $data;
}