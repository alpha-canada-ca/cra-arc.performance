<?php

function days_since_today($date): int {
    return round((time() - $date) / (60 * 60 * 24));
}

// Periodically check the cache and remove files that are older than a certain amount of days
function check_cache($cache_files) {
    $cache_path = './cache/';
    $lastChecked_path = $cache_path . 'lastChecked';
    $checking_interval = 7; // in days
    $max_age = 40; // in days

    // create a file to keep track of the last cache check- to avoid having to do it on each API call
    if (!in_array($lastChecked_path, $cache_files)) {
        // The file will contain an integer representing the unix timestamp of the last cache check
        file_put_contents($lastChecked_path, 0);
    }

    if (days_since_today(intval(file_get_contents($lastChecked_path))) >= $checking_interval) {
        foreach ($cache_files as $filename) {
            // if the last modified time of the file is greater than $max_age, delete it
            if (days_since_today(filemtime($cache_path . $filename)) >= $max_age) {
                unlink($cache_path . $filename);
            }
        }
    }
}

function get_aa_data($json, $apiClient) {
    $cache_path = './cache/';

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