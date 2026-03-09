<?php

namespace App\Models;
use PDO;

use App\Core\Model;

class DatosPersonales extends Model
{
    // Indica el nombre de la tabla
    protected static string $table = 'datospersonales';

    /**
     * Crea un registro para los datos personales de un usuario.
     *
     * @param int $userId El id del usuario.
     * @return bool Resultado de la inserción.
     */
    public static function createForUser(int $userId): bool
    {
        // Solo seteamos user_id; los demás campos se dejarán en NULL (o valor por defecto)
        $data = [
            'user_id' => $userId
        ];
        return self::create($data);
    }

    public static function findByUserId(int $id): ?array
    {
        $db = self::getDB();

        $stmt = $db->query("SELECT * FROM datospersonales WHERE user_id = ". $id);
        //$stmt->execute();
        $legajo = $stmt->fetch();
        return $legajo ? $legajo : null;
    }

    public static function findByUserIdWithRole(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->query("SELECT d.*, u.role FROM datospersonales d, users u WHERE d.user_id = ". $id . " and d.user_id = u.id");
        //$stmt->execute([$id]);
        $legajo = $stmt->fetch();
        return $legajo ? $legajo : null;
    }


    public static function updatebyUser(int $userId, array $data): bool
    
    {
            $set = [];
            foreach ($data as $column => $value) {
                $set[] = "$column = ?";
            }
            $setClause = implode(', ', $set);
    
            $stmt = self::getDB()->prepare("UPDATE " . static::$table . " SET $setClause WHERE user_id = $userId" );
            $values = array_values($data);
            
    
            return $stmt->execute($values);
    }
    

    public static function todosconmail(): ?array
    {
        $db = self::getDB();
        $stmt = $db->query("SELECT datospersonales.*, users.email FROM datospersonales, users WHERE datospersonales.user_id = users.id ");
        $stmt->execute([]);
        $padron = $stmt->fetch();
        return $padron ? $padron : null;
    }
    public static function QrySingleRec(string $customquery): array
    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
    public static function findByUser(int $id): ?array
    {
        $table = static::$table ?? strtolower(static::class);

        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    
    public static function GetNombreById(int $id): ?string
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT concat(apellido, ' ', nombre) as nombre_completo FROM datospersonales WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        return $person['nombre_completo'] ?: '';
    }

    public static function GetByUserId(int $id): ?string
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM datospersonales WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        return $person ?: null;
    }
    
    public static function GetNombreByUserId(int $id): ?string
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT concat(apellido, ' ', nombre) as nombre_completo FROM datospersonales WHERE user_id =" . $id);
        $stmt->execute([]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        return $person['nombre_completo'] ?: '';
    }

    public static function faltandatos(int $id): ?bool
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM datospersonales WHERE user_id = ?");
        $stmt->execute([$id]);
        $dp = $stmt->fetch();
        $faltan = $dp['apellido'] == null or $dp['apellido'] == '' or $dp['nombre'] == null or $dp['nombre'] == '';
        if (!$faltan) {
            $faltan = $dp['direccion_calle'] == null or $dp['direccion_calle'] == '' or $dp['direccion_numero'] == null or $dp['direccion_numero'] == '';
        }
        if (!$faltan) {
            $faltan = $dp['ciudad_id'] == null or $dp['provincia_id'] == null ;
        }

        //
        return $faltan ? true : false;
    }
 
    public function crearcarnets(Request $request): void
    {
    }



    

}
