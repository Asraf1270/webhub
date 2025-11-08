<?php
define('SEEN_FILE', __DIR__ . '/../data/seen.json');

function getSeenData(): array {
    if (!file_exists(SEEN_FILE)) {
        $default = ['global' => [], 'users' => []];
        file_put_contents(SEEN_FILE, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $json = file_get_contents(SEEN_FILE);
    return json_decode($json, true) ?: ['global' => [], 'users' => []];
}

function saveSeenData(array $data): bool {
    return file_put_contents(SEEN_FILE, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}