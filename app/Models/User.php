<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    // Definir el nombre de la tabla.
    protected static string $table = 'users';

    /**
     * Crea un nuevo usuario (método personalizado si quieres, 
     * también podés usar directamente Model::create($data)).
     */
    public static function createUser(string $email, string $passwordHash, string $activationToken): bool
    {
        $data = [
            'email'            => $email,
            'password'         => $passwordHash,
            'activation_token' => $activationToken,
            'active'           => 0  // 0 = inactivo, 1 = activo
        ];
        return self::create($data);
    }

    /**
     * Busca un usuario por token de activación.
     */
    public static function findByActivationToken( string $token): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE activation_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    /**
     * Activa el usuario.
     */
    public static function activate(int $userId): bool
    {
        $data = [
            'active' => 1,
            'activation_token' => null,
            'mailed' => date('Y/m/d')
        ];
        return self::update($userId, $data);
    }
    public static function findByEmail(string $email): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    return $user ? $user : null;
    }

// 1) Guarda token y expiración (1 hora por ejemplo)
    public static function setPasswordResetToken(string $email, string $token, string $expires): bool
    {
        $db = self::getDB();
        $sql = "UPDATE " . static::$table . "
                SET password_reset_token = :token,
                    password_reset_expires_at = :expires
                WHERE email = :email";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':token'   => $token,
            ':expires' => $expires,
            ':email'   => $email,
        ]);
    }

    // 2) Busca usuario válido por token
    public static function findByResetToken(string $token): ?array
    {
        $db = self::getDB();
        $sql = "SELECT * FROM " . static::$table . "
                WHERE password_reset_token = :token
                  AND password_reset_expires_at >= NOW()
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // 3) Limpia token y expiración tras reset
    public static function clearPasswordResetToken(int $userId): bool
    {
        $db = self::getDB();
        $sql = "UPDATE " . static::$table . "
                SET password_reset_token = NULL,
                    password_reset_expires_at = NULL
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    // 4) Actualiza la contraseña del usuario
    public static function updatePassword(int $userId, string $newHash): bool
    {
        $db = self::getDB();
        $sql = "UPDATE " . static::$table . "
                SET password = :pw
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':pw' => $newHash,
            ':id' => $userId,
        ]);
    }

    public static function updateRole(int $userId, string $role):bool
    {
        $db = self::getDB();
        $sql = "UPDATE users SET role = ? WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([$role, $userId]);

    }

        public static function findPending(): ?array
    {
        $db = self::getDB();
        $stmt = $db->query("SELECT u.id, u.email, u.created_at FROM users u WHERE (u.activation_token = null) or u.active =0 order by u.created_at desc");
        $stmt->execute();
        $results = $stmt->fetch();
        return $results ? $results : null;
    }

    public static function CustomQry(string $customquery): array

    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public static function CustomError(): string

    {
        $localerror = self::getDB()->errorInfo();
        return $localerror[2] ?? 'Error desconocido en la consulta SQL';

    }
    public static function GetEmail(int $id): ?string
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT email FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ? $user['email'] : null;
    }


}