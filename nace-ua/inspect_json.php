<?php
// Read and inspect the downloaded JSON file
$raw = file_get_contents('kv10.json');
if (!$raw) {
    echo "ERROR: kv10.json not found or empty\n";
    exit(1);
}
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON parse error: " . json_last_error_msg() . "\n";
    echo "Raw first 500 chars:\n";
    echo substr($raw, 0, 500) . "\n";
    exit(1);
}

echo "JSON structure type: " . (is_array($data) ? 'Array' : 'Object') . "\n";
if (is_array($data)) {
    echo "Total top-level entries: " . count($data) . "\n";
    echo "Keys of first entry: " . implode(', ', array_keys($data[0] ?? (array)$data)) . "\n";
    echo "First 3 entries:\n";
    print_r(array_slice($data, 0, 3));
}
