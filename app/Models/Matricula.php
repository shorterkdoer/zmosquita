<?php

namespace App\Models;

use App\Core\Model;

class Matricula extends Model
{
    // Indica el nombre de la tabla
    protected static string $table = 'matriculas';

    /**
     * Crea un registro para la matrícula de un usuario.
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
    public static function CustomQry(string $customquery): array

    {
        $stmt = self::getDB()->query($customquery);
        return $stmt->fetch();
    }

    public static function findByUserId(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE user_id = ?");
        $stmt->execute([$id]);
        $matricula = $stmt->fetch();
        return $matricula ? $matricula : null;
    }

    public static function findByAsignada(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM " . static::$table . " WHERE matriculaasignada = ?");
        $stmt->execute([$id]);
        $matricula = $stmt->fetch();
        return $matricula ? $matricula : null;
    }

    public static function freezedata(int $id): bool
    {
        $db = self::getDB();
        $xfecha = date('Y-m-d'); // Asegúrate de que este campo exista en $data
        $mysql = "UPDATE " . static::$table . " SET freezedata = '". $xfecha ."'  WHERE user_id = ". $id;
        $stmt = $db->prepare( $mysql);
            //$values = array_values($data);
        return $stmt->execute(); 


    }

    public static function statusmatricula(int $id): string
    {
        $db = self::getDB();
        //freezedata = fecha cuando se pidió la revision
        //aprobado = fecha de otorgamiento de matriculacion
        //baja = fecha de baja
        //revision = fecha en que un admin toma para revision
        //verificado = fecha en que un admin toma para verificación física

        $stmt = $db->prepare("SELECT freezedata, aprobado, baja, revision, verificado, estado FROM " . static::$table . " WHERE user_id = ?");
        $stmt->execute([$id]);
        $matricula = $stmt->fetch();
        $zzestado = '';
        if ($matricula['freezedata'] == null){
            $zzestado = '';
        }elseif ($matricula['baja'] != null) {
            $zzestado = 'Baja';
        }elseif ($matricula['revision'] != null) {
            $zzestado = 'Revisión';
        }elseif ($matricula['verificado'] != null) {
            $zzestado = 'Verificado';
        }elseif ($matricula['aprobado'] != null) {
                $zzestado = 'Activa';
                if ($matricula['estado'] != null) { // puede ser 'Suspendida' o 'Inhabilitada'
                    $zzestado .= ' - '. $matricula['estado'];
                }
        }elseif ($matricula['freezedata'] != null) {
            $zzestado = 'Solicitada';
        }
        // '' - Baja - Revisión - Verificado - Activa - Solicitada

        return $zzestado;


    }

    public static function updateMatriculabyUser(int $userId, array $data): bool
  {

            $db = self::getDB();
            $xmat = $data['matriculaasignada'] ?? null; // Asegúrate de que este campo exista en $data

            if ($xmat === null) {
                throw new \InvalidArgumentException("El campo 'matriculaasignada' es obligatorio.");
            }

            $xfecha = $data['aprobado'] ?? null; // Asegúrate de que este campo exista en $data
            $mysql = "UPDATE " . static::$table . " SET matriculaasignada = ". $xmat . " , aprobado = '" . $xfecha ."'  WHERE user_id = ". $userId;
            $stmt = $db->prepare( $mysql);
            //$values = array_values($data);

    
            return $stmt->execute(); 
            /* $stmt->execute([ 
                ':xmat' => $xmat,
                ':xfec' => $xfecha
            ]); */
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

    public static function getUserIdById(int $id): ?int
    {
        $db = self::getDB(); // Asegúrate de tener este método en tu modelo base
        $stmt = $db->prepare("SELECT user_id FROM matriculas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        return $result ? (int)$result['user_id'] : null;
    }
    public static function getMatriculaIdById(int $id): ?int
    {
        $db = self::getDB(); // Asegúrate de tener este método en tu modelo base
        $stmt = $db->prepare("SELECT matriculaasignada FROM matriculas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();

        return $result ? (int)$result['matriculaasignada'] : null;
    }


    public static function getMatriculaByUserId(int $id): ?array
    {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT matriculaasignada FROM " . static::$table . " WHERE user_id = ?");
        $stmt->execute([$id]);
        $matricula = $stmt->fetch();
        return $matricula ? $matricula : null;
    }

    /* public static function findByUser(int $id): ?array
        {
            $table = static::$table ?? strtolower(static::class);

            $db = self::getDB();
            $stmt = $db->prepare("SELECT * FROM `$table` WHERE user_id = :id");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ?: null;
        }
*/
}
