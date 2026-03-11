<?php

/**
 * Test Helper Functions
 * Common utilities used across tests
 */

/**
 * Create a test user in the database
 */
function createTestUser(array $data = []): array
{
    $defaults = [
        'email' => 'test_' . time() . '@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'role' => 'user',
        'activo' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $userData = array_merge($defaults, $data);

    // This would typically use a repository or model
    // For now, return mock data
    return $userData;
}

/**
 * Create a test matricula in the database
 */
function createTestMatricula(int $userId, array $data = []): array
{
    $defaults = [
        'user_id' => $userId,
        'estado' => 'Pendiente',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    return array_merge($defaults, $data);
}

/**
 * Get a mock database connection
 */
function getMockDb(): PDO
{
    return new PDO('sqlite::memory:');
}

/**
 * Authenticate a test user in session
 */
function authenticateTestUser(array $user): void
{
    $_SESSION['user'] = [
        'id' => $user['id'] ?? 1,
        'email' => $user['email'] ?? 'test@example.com',
        'role' => $user['role'] ?? 'user',
    ];
}

/**
 * Clear test session
 */
function clearTestSession(): void
{
    $_SESSION = [];
}

/**
 * Get test config value
 */
function getTestConfig(string $key, $default = null)
{
    $config = require TEST_ROOT . '/../config/settings.php';
    return $config[$key] ?? $default;
}
