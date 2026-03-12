<?php

namespace App\Tests\Unit\Services;

use App\Services\AuthService;
use App\Services\EmailService;
use App\Repositories\UserRepository;
use App\Repositories\DatosPersonalesRepository;
use App\Repositories\MatriculaRepository;
use PHPUnit\Framework\TestCase;

/**
 * AuthService Unit Tests
 */
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['directoriobase'] = dirname(__DIR__, 3);
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
     * Test logout clears session
     */
    public function testLogoutClearsSession(): void
    {
        // Set up a user in session
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        // Call logout
        $this->authService->logout();

        // Assert user is removed from session
        $this->assertArrayNotHasKey('user', $_SESSION);
    }

    /**
     * Test isAuthenticated returns false when no user in session
     */
    public function testIsAuthenticatedReturnsFalseWhenNoUserInSession(): void
    {
        // Clear session
        $_SESSION = [];

        $result = $this->authService->isAuthenticated();

        $this->assertFalse($result);
    }

    /**
     * Test isAuthenticated returns true when user in session
     */
    public function testIsAuthenticatedReturnsTrueWhenUserInSession(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        $result = $this->authService->isAuthenticated();

        $this->assertTrue($result);
    }

    /**
     * Test currentUser returns null when no user in session
     */
    public function testCurrentUserReturnsNullWhenNoUserInSession(): void
    {
        $_SESSION = [];

        $result = $this->authService->currentUser();

        $this->assertNull($result);
    }

    /**
     * Test currentUser returns user array when user in session
     */
    public function testCurrentUserReturnsUserArrayWhenUserInSession(): void
    {
        $testUser = ['id' => 1, 'email' => 'test@example.com', 'role' => 'user'];
        $_SESSION['user'] = $testUser;

        $result = $this->authService->currentUser();

        $this->assertIsArray($result);
        $this->assertEquals($testUser, $result);
    }

    /**
     * Test hasRole returns false when no user in session
     */
    public function testHasRoleReturnsFalseWhenNoUserInSession(): void
    {
        $_SESSION = [];

        $result = $this->authService->hasRole('admin');

        $this->assertFalse($result);
    }

    /**
     * Test hasRole returns false when user doesn't have role
     */
    public function testHasRoleReturnsFalseWhenUserDoesNotHaveRole(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com', 'role' => 'user'];

        $result = $this->authService->hasRole('admin');

        $this->assertFalse($result);
    }

    /**
     * Test hasRole returns true when user has role
     */
    public function testHasRoleReturnsTrueWhenUserHasRole(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@example.com', 'role' => 'admin'];

        $result = $this->authService->hasRole('admin');

        $this->assertTrue($result);
    }

    /**
     * Test getCurrentRole returns null when no user in session
     */
    public function testGetCurrentRoleReturnsNullWhenNoUserInSession(): void
    {
        $_SESSION = [];

        $result = $this->authService->getCurrentRole();

        $this->assertNull($result);
    }

    /**
     * Test getCurrentRole returns role when user in session
     */
    public function testGetCurrentRoleReturnsRoleWhenUserInSession(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@example.com', 'role' => 'admin'];

        $result = $this->authService->getCurrentRole();

        $this->assertEquals('admin', $result);
    }

    /**
     * Test login returns error for invalid credentials
     */
    public function testLoginReturnsErrorForInvalidCredentials(): void
    {
        $result = $this->authService->login('nonexistent@example.com', 'wrongpassword');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
        $this->assertNull($result['user']);
    }

    /**
     * Test register fails for invalid email
     */
    public function testRegisterFailsForInvalidEmail(): void
    {
        $result = $this->authService->register('invalid-email', 'StrongPass123!');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }

    /**
     * Test register fails for weak password
     */
    public function testRegisterFailsForWeakPassword(): void
    {
        $result = $this->authService->register('test@example.com', 'weak');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }

    /**
     * Test activate fails for invalid token
     */
    public function testActivateFailsForInvalidToken(): void
    {
        $result = $this->authService->activate('invalid-token');

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }
}
