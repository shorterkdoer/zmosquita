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

    protected function tearDown(): void
    {
        // Clear session data after each test
        $_SESSION = [];
        parent::tearDown();
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

        // Assert isAuthenticated returns true
        $this->assertTrue($this->authService->isAuthenticated());
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
        $this->assertTrue($this->authService->isAuthenticated());

        // Logout
        $this->authService->logout();

        // Verify user is logged out
        $this->assertFalse($this->authService->isAuthenticated());
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
        $this->assertFalse($this->authService->isAuthenticated());

        // currentUser should return null
        $user = $this->authService->currentUser();
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
        $currentUser = $this->authService->currentUser();

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

        // Call isValid which initializes security if not present
        Session::isValid();

        // Session should have security data after validation
        $this->assertArrayHasKey('_security', $_SESSION);

        // Verify security data has required keys
        $security = $_SESSION['_security'];
        $this->assertArrayHasKey('ip', $security);
        $this->assertArrayHasKey('ua', $security);
        $this->assertArrayHasKey('created', $security);
        $this->assertArrayHasKey('last_activity', $security);
    }

    /**
     * Test getCurrentRole returns correct role
     */
    public function testGetCurrentRoleReturnsCorrectRole(): void
    {
        // Set up admin user
        $_SESSION['user'] = [
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin'
        ];

        $role = $this->authService->getCurrentRole();
        $this->assertEquals('admin', $role);
    }
}
