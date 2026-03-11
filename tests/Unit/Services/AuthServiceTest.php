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
    private EmailService $emailServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock email service
        $this->emailServiceMock = $this->createMock(EmailService::class);

        $this->authService = new AuthService(
            $this->emailServiceMock
        );
    }

    /**
     * Test that validateEmail returns false for invalid email
     */
    public function testValidateEmailReturnsFalseForInvalidEmail(): void
    {
        $result = $this->authService->validateEmail('invalid-email');
        $this->assertFalse($result);
    }

    /**
     * Test that validateEmail returns true for valid email
     */
    public function testValidateEmailReturnsTrueForValidEmail(): void
    {
        $result = $this->authService->validateEmail('test@example.com');
        $this->assertTrue($result);
    }

    /**
     * Test that validatePassword returns false for weak password
     */
    public function testValidatePasswordReturnsFalseForWeakPassword(): void
    {
        $result = $this->authService->validatePassword('123');
        $this->assertFalse($result);
    }

    /**
     * Test that validatePassword returns true for strong password
     */
    public function testValidatePasswordReturnsTrueForStrongPassword(): void
    {
        $result = $this->authService->validatePassword('StrongPass123!');
        $this->assertTrue($result);
    }

    /**
     * Test that validatePassword returns errors for weak password
     */
    public function testValidatePasswordReturnsErrorsForWeakPassword(): void
    {
        $result = $this->authService->validatePassword('weak');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
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
     * Test logout returns success array
     */
    public function testLogoutReturnsSuccessArray(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        $result = $this->authService->logout();

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNull($result['error']);
    }

    /**
     * Test isLoggedIn returns false when no user in session
     */
    public function testIsLoggedInReturnsFalseWhenNoUserInSession(): void
    {
        // Clear session
        $_SESSION = [];

        $result = $this->authService->isLoggedIn();

        $this->assertFalse($result);
    }

    /**
     * Test isLoggedIn returns true when user in session
     */
    public function testIsLoggedInReturnsTrueWhenUserInSession(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com'];

        $result = $this->authService->isLoggedIn();

        $this->assertTrue($result);
    }

    /**
     * Test getCurrentUser returns null when no user in session
     */
    public function testGetCurrentUserReturnsNullWhenNoUserInSession(): void
    {
        $_SESSION = [];

        $result = $this->authService->getCurrentUser();

        $this->assertNull($result);
    }

    /**
     * Test getCurrentUser returns user array when user in session
     */
    public function testGetCurrentUserReturnsUserArrayWhenUserInSession(): void
    {
        $testUser = ['id' => 1, 'email' => 'test@example.com', 'role' => 'user'];
        $_SESSION['user'] = $testUser;

        $result = $this->authService->getCurrentUser();

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
     * Test requireRole throws exception when user doesn't have role
     */
    public function testRequireRoleThrowsExceptionWhenUserDoesNotHaveRole(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Acceso no autorizado');

        $_SESSION['user'] = ['id' => 1, 'email' => 'test@example.com', 'role' => 'user'];

        $this->authService->requireRole('admin');
    }

    /**
     * Test requireRole does nothing when user has role
     */
    public function testRequireRoleDoesNothingWhenUserHasRole(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@example.com', 'role' => 'admin'];

        $this->assertNull($this->authService->requireRole('admin'));
    }
}
