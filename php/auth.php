<?php

require_once __DIR__ . '/functions.php';

define('USERS_FILE', dirname(__DIR__) . '/data/users.json');

function registerUser($name, $email, $password) {
    $name  = sanitize($name);
    $email = strtolower(trim($email));

    if (empty($name) || empty($email) || empty($password)) return false;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         return false;
    if (strlen($password) < 6)                              return false;

    $users = readJson(USERS_FILE);
    foreach ($users as $u) {
        if (strtolower($u['email']) === $email) return false;
    }

    $user = [
        'id'             => generateId(),
        'name'           => $name,
        'email'          => $email,
        'password'       => password_hash($password, PASSWORD_DEFAULT),
        'preferred_unit' => 'kg',
        'created_at'     => date('Y-m-d H:i:s'),
    ];

    $users[] = $user;
    writeJson(USERS_FILE, $users);

    return $user;
}

function loginUser($email, $password) {
    $email = strtolower(trim($email));
    if (empty($email) || empty($password)) return false;

    $users = readJson(USERS_FILE);
    foreach ($users as $u) {
        if (strtolower($u['email']) === $email && password_verify($password, $u['password'])) {
            return $u;
        }
    }
    return false;
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    $users = readJson(USERS_FILE);
    foreach ($users as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function requireGuest() {
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}

function updateProfile($userId, $data) {
    $users = readJson(USERS_FILE);
    foreach ($users as &$u) {
        if ($u['id'] !== $userId) continue;
        if (!empty($data['name']))  $u['name']  = sanitize($data['name']);
        if (!empty($data['email'])) $u['email'] = strtolower(trim($data['email']));
        if (!empty($data['unit']))  $u['preferred_unit'] = $data['unit'] === 'lbs' ? 'lbs' : 'kg';
        break;
    }
    unset($u);
    return writeJson(USERS_FILE, $users);
}

function changePassword($userId, $currentPw, $newPw) {
    $users = readJson(USERS_FILE);
    foreach ($users as &$u) {
        if ($u['id'] !== $userId) continue;
        if (!password_verify($currentPw, $u['password'])) return false;
        if (strlen($newPw) < 6) return false;
        $u['password'] = password_hash($newPw, PASSWORD_DEFAULT);
        break;
    }
    unset($u);
    return writeJson(USERS_FILE, $users);
}

function deleteAccount($userId) {
    $users = readJson(USERS_FILE);
    $users = array_values(array_filter($users, fn($u) => $u['id'] !== $userId));
    writeJson(USERS_FILE, $users);

    $dataDir      = dirname(__DIR__) . '/data/';
    $userFile     = $dataDir . "user_{$userId}.json";
    $workoutsFile = $dataDir . "workouts_{$userId}.json";
    if (file_exists($userFile))     unlink($userFile);
    if (file_exists($workoutsFile)) unlink($workoutsFile);

    return true;
}