<?php

function generateId()
{
    return uniqid('usr_', true);
}

function readJson($file)
{
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

function writeJson($file, $data)
{
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

function sanitize($str)
{
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}

function formatDate($date)
{
    $ts = strtotime($date);
    if (!$ts) return $date;
    return date('d M Y', $ts);
}

function formatDateRo($date)
{
    $months = [
        1  => 'Ianuarie',
        2  => 'Februarie',
        3  => 'Martie',
        4  => 'Aprilie',
        5  => 'Mai',
        6  => 'Iunie',
        7  => 'Iulie',
        8  => 'August',
        9  => 'Septembrie',
        10 => 'Octombrie',
        11 => 'Noiembrie',
        12 => 'Decembrie',
    ];
    $days = [
        0 => 'Duminică',
        1 => 'Luni',
        2 => 'Marți',
        3 => 'Miercuri',
        4 => 'Joi',
        5 => 'Vineri',
        6 => 'Sâmbătă',
    ];
    $ts = strtotime($date);
    return $days[date('w', $ts)] . ', ' . date('d', $ts) . ' '
        . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function getGreeting()
{
    $h = (int)date('H');
    if ($h >= 5 && $h < 12)  return 'dimineața';
    if ($h >= 12 && $h < 18) return 'după-amiaza';
    return 'seara';
}

function kgToLbs($kg)
{
    return round((float)$kg * 2.20462, 1);
}

function lbsToKg($lbs)
{
    return round((float)$lbs / 2.20462, 2);
}
function calculateVolume($sets)
{
    $volume = 0;
    foreach ($sets as $set) {
        $volume += ($set['weight'] ?? 0) * ($set['reps'] ?? 0);
    }
    return $volume;
}

function formatDuration($minutes)
{
    if ($minutes < 60) return $minutes . ' min';
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'h ' . ($mins > 0 ? $mins . 'min' : '');
}

function findPersonalRecord($workouts, $exerciseName)
{
    $maxWeight = 0;
    $bestSet = null;
    foreach ($workouts as $w) {
        foreach ($w['exercises'] as $ex) {
            if (strtolower(trim($ex['name'])) === strtolower(trim($exerciseName))) {
                foreach ($ex['sets'] as $set) {
                    $weight = (float)($set['weight'] ?? 0);
                    if ($weight > $maxWeight) {
                        $maxWeight = $weight;
                        $bestSet = $set;
                    }
                }
            }
        }
    }
    return $bestSet ? ['weight' => $maxWeight, 'reps' => $bestSet['reps']] : null;
}

function getLastWorkoutDate($workouts)
{
    if (empty($workouts)) return null;
    usort($workouts, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    return $workouts[0]['date'];
}

function isValidWorkout($workout)
{
    if (empty($workout['name']) || empty($workout['exercises'])) return false;
    foreach ($workout['exercises'] as $ex) {
        if (empty($ex['name']) || empty($ex['sets'])) return false;
        foreach ($ex['sets'] as $set) {
            if (!isset($set['reps']) || !isset($set['weight'])) return false;
            if ($set['reps'] < 1 || $set['weight'] < 0) return false;
        }
    }
    return true;
}

function getTotalExercisesCount($workouts)
{
    $count = 0;
    foreach ($workouts as $w) {
        $count += count($w['exercises']);
    }
    return $count;
}

function getTotalSetsCount($workouts)
{
    $count = 0;
    foreach ($workouts as $w) {
        foreach ($w['exercises'] as $ex) {
            $count += count($ex['sets']);
        }
    }
    return $count;
}

function getTotalVolume($workouts)
{
    $total = 0;
    foreach ($workouts as $w) {
        foreach ($w['exercises'] as $ex) {
            $total += calculateVolume($ex['sets']);
        }
    }
    return $total;
}

function getAverageWeightPerExercise($workouts, $exerciseName)
{
    $weights = [];
    foreach ($workouts as $w) {
        foreach ($w['exercises'] as $ex) {
            if (strtolower(trim($ex['name'])) === strtolower(trim($exerciseName))) {
                foreach ($ex['sets'] as $set) {
                    $weights[] = (float)($set['weight'] ?? 0);
                }
            }
        }
    }
    if (empty($weights)) return 0;
    return round(array_sum($weights) / count($weights), 2);
}
