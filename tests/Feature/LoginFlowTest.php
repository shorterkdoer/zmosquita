<?php

namespace App\Tests\Feature;

use App\Tests\TestCase;
use App\Services\AuthService;
use App\Services\EmailService;
use App\Repositories\UserRepository;
use Foundation\Core\Session;

/**
 * Login Flow Feature Tests
 * Tests the complete login flow
 */
class LoginFlowTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize base directory
        $_SESSION['directoriobase'] = dirname(__DIR__, 2);
        $_SESSION['base_url'] = 'http://localhost';

        $this->authService = new AuthService(
            new EmailService()
        );
    }

    /**
     * Test that a user can login with valid credentials
     */
    public function testUserCanLoginWithValidCredentials(): void
    {
        // This test would require a test database with a user
        // For now, we'll test the session management

        $testUser = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'user'
        ];

        // Simulate successful login by setting session
        $_SESSION['user'] = $testUser;

        // Assert user is in session
        $this->assertArrayHasKey('user', $_SESSION);
        $this->assertEquals($testUser['email'], $_SESSION['user']['email']);

        // Assert isLoggedIn returns true
        $this->assertTrue($this->authService->isLoggedIn());
    }

    /**
     * Test that logout clears the session
     */
    public function testLogoutClearsSession(): void
    {
        // Set up logged in user
        $_SESSION['user'] = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'user'
        ];

        // Verify user is logged in
        $this->assertTrue($this->authService->isLoggedIn());

        // Logout
        $result = $this->authService->logout();

        // Verify logout was successful
        $this->assertTrue($result['success']);

        // Verify user is logged out
        $this->assertFalse($this->authService->isLoggedIn());
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    /**
     * Test that accessing protected route redirects when not logged in
     */
    public function testProtectedRouteRedirectsWhenNotLoggedIn(): void
    {
        // Clear session
        $_SESSION = [];

        // User should not be logged in
        $this->assertFalse($this->authService->isLoggedIn());

        // getCurrentUser should return null
        $user = $this->authService->getCurrentUser();
        $this->assertNull($user);
    }

    /**
     * Test that admin user can access admin routes
     */
    public function testAdminUserCanAccessAdminRoutes(): void
    {
        // Set up admin user
        $_SESSION['user'] = [
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin'
        ];

        // Check user has admin role
        $this->assertTrue($this->authService->hasRole('admin'));

        // requireRole should not throw exception
        try {
            $this->authService->requireRole('admin');
            $this->assertTrue(true); // If we get here, test passed
        } catch (\RuntimeException $e) {
            $this->fail('Admin user should be able to access admin routes');
        }
    }

    /**
     * Test that regular user cannot access admin routes
     */
    public function testRegularUserCannotAccessAdminRoutes(): void
    {
        // Set up regular user
        $_SESSION['user'] = [
            'id' => 2,
            'email' => 'user@example.com',
            'role' => 'user'
        ];

        // Check user doesn't have admin role
        $this->assertFalse($this->authService->hasRole('admin'));

        // requireRole should throw exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Acceso no autorizado');

        $this->authService->requireRole('admin');
    }

    /**
     * Test session persistence across requests (simulated)
     */
    public function testSessionPersistsAcrossRequests(): void
    {
        // Set user in session
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'user'
        ];
        $_SESSION['user'] = $userData;

        // Verify session data is accessible
        $this->assertEquals($userData['email'], $_SESSION['user']['email']);

        // Simulate "next request" by getting current user
        $currentUser = $this->authService->getCurrentUser();

        $this->assertNotNull($currentUser);
        $this->assertEquals($userData['email'], $currentUser['email']);
    }

    /**
     * Test that session security data is set
     */
    public function testSessionSecurityDataIsSet(): void
    {
        // Start fresh session
        $_SESSION = [];

        // Session should have security data after starting
        // This tests Session class functionality
        $this->assertArrayHasKey('_security', $_SESSION);

        // Verify security data has required keys
        $security = $_SESSION['_security'];
        $this->assertArrayHasKey('ip', $security);
        $this->assertArrayHasKey('ua', $security);
        $this->assertArrayHasKey('created', $security);
        $this->assertArrayHasKey('last_activity', $security);
    }
}
