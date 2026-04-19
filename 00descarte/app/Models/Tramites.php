<?php



namespace App\Models;

use PDO;

use App\Core\Model;

class Tramites extends Model
{
    // Nombre de la tabla en la base de datos
    protected static string $table = 'tramites';

    public static function findById(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        $tramites = $stmt->fetch();
        return $tramites ? $tramites : null;
    }

    public static function CustomQry(string $customquery): array

    {
        
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public static function nuevo(array $data): bool
    {


        $columnas = array_keys($data);

        // 2. Generás una cadena de placeholders con la misma cantidad de elementos
        $placeholders = rtrim(str_repeat('?,', count($data)), ',');

        // 3. Armás el SQL
        $sql = "INSERT INTO tramites (" . implode(', ', $columnas) . ") 
                VALUES ($placeholders)";

/*        $columns = array_keys($data);
        $placeholders = array_values($data);
        //$columnsList = implode(', ', $columns);

        $sql = "INSERT INTO tramites ($columns) VALUES ($placeholders)";
*/
        // 4. Preparás y ejecutás la consulta
        $stmt = self::getDB()->prepare($sql);
        return $stmt->execute(array_values($data));
    }



}
