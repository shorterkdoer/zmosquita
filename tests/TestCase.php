<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case
 * All test classes should extend this class
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear session before each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        // Clear session after each test
        $_SESSION = [];

        parent::tearDown();
    }

    /**
     * Authenticate a test user
     */
    protected function authenticateUser(array $user = []): void
    {
        $defaults = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'user',
        ];

        $_SESSION['user'] = array_merge($defaults, $user);
    }

    /**
     * Set session values
     */
    protected function setSession(array $data): void
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Get session value
     */
    protected function getSession(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Assert that session has a key
     */
    protected function assertSessionHas(string $key): void
    {
        $this->assertArrayHasKey($key, $_SESSION);
    }

    /**
     * Assert that session has a value
     */
    protected function assertSessionEquals(string $key, $expected): void
    {
        $this->assertEquals($expected, $_SESSION[$key] ?? null);
    }

    /**
     * Create a mock PDO instance
     */
    protected function createMockPdo(): \PDO
    {
        return $this->createMock(\PDO::class);
    }

    /**
     * Create a mock PDO statement
     */
    protected function createMockPDOStatement(): \PDOStatement
    {
        return $this->createMock(\PDOStatement::class);
    }

    /**
     * Get service instance by class name
     */
    protected function getService(string $serviceClass): object
    {
        return new $serviceClass();
    }

    /**
     * Get repository instance by class name
     */
    protected function getRepository(string $repositoryClass): object
    {
        return new $repositoryClass();
    }
}
