<?php
$title = '13 Geister';
$year = 2001;
$hash = md5($title . $year);
$path = 'q:/cloud.neuhaus.or.at/repos/dvd/versions/dvdprofiler.liste/cache/tmdb/' . $hash . '.json';
echo "Hash: $hash\n";
echo "Path: $path\n";
if (file_exists($path)) {
    echo "File exists!\n";
    $json = file_get_contents($path);
    echo "Content: $json\n";
    $data = json_decode($json, true);
    $rating = $data['vote_average'] ?? $data['tmdb_rating'] ?? 'NOT FOUND';
    echo "Rating: $rating\n";
} else {
    echo "File not found!\n";
}
