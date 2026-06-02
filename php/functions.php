<?php

function generateId() {
    return uniqid('usr_', true);
}

function readJson($file) {
    if (!file_exists($file)) return [];
    $fp = fopen($file, 'r');
    if (!$fp) return [];
    flock($fp, LOCK_SH);
    $contents = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    if (empty($contents)) return [];
    $data = json_decode($contents, true);
    return is_array($data) ? $data : [];
}

function writeJson($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fp = fopen($file, 'w');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function sanitize($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    $ts = strtotime($date);
    if (!$ts) return $date;
    return date('d M Y', $ts);
}

function formatDateRo($date) {
    $months = [
        1  => 'Ianuarie', 2  => 'Februarie', 3  => 'Martie',
        4  => 'Aprilie',  5  => 'Mai',        6  => 'Iunie',
        7  => 'Iulie',    8  => 'August',     9  => 'Septembrie',
        10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie',
    ];
    $days = [
        0 => 'Duminică', 1 => 'Luni', 2 => 'Marți',
        3 => 'Miercuri', 4 => 'Joi',  5 => 'Vineri', 6 => 'Sâmbătă',
    ];
    $ts = strtotime($date);
    return $days[date('w', $ts)] . ', ' . date('d', $ts) . ' '
         . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function getGreeting() {
    $h = (int)date('H');
    if ($h >= 5 && $h < 12)  return 'dimineața';
    if ($h >= 12 && $h < 18) return 'după-amiaza';
    return 'seara';
}

function kgToLbs($kg) {
    return round((float)$kg * 2.20462, 1);
}

function lbsToKg($lbs) {
    return round((float)$lbs / 2.20462, 2);
}