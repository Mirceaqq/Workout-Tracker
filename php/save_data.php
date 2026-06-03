<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/functions.php';

define('WORKOUTS_FILE', dirname(__DIR__) . '/data/workouts.json');

function getWorkouts($userId)
{
    $all = readJson(WORKOUTS_FILE);
    return array_values(array_filter($all, fn($w) => ($w['user_id'] ?? '') === $userId));
}

function saveWorkout($userId, array $workout)
{
    $workout['user_id'] = $userId;
    $all = readJson(WORKOUTS_FILE);
    $all[] = $workout;
    return writeJson(WORKOUTS_FILE, $all);
}

function deleteWorkout($userId, $workoutId)
{
    $all = readJson(WORKOUTS_FILE);
    $all = array_values(array_filter(
        $all,
        fn($w) => !($w['id'] === $workoutId && ($w['user_id'] ?? '') === $userId)
    ));
    return writeJson(WORKOUTS_FILE, $all);
}

function deleteUserWorkouts($userId)
{
    $all = readJson(WORKOUTS_FILE);
    $all = array_values(array_filter($all, fn($w) => ($w['user_id'] ?? '') !== $userId));
    return writeJson(WORKOUTS_FILE, $all);
}
