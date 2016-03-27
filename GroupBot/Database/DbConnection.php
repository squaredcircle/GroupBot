<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 22/02/2016
 * Time: 7:31 PM
 */

namespace GroupBot\Database;


class DbConnection
{
    /** @var \PDO  */
    protected $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Do not use when SPLEnum is involved. Works fine for VERY simple classes.
     * @param $db_name
     * @param $object
     * @return bool
     */
    protected function updateObject($db_name, $object)
    {
        $sql = "INSERT INTO $db_name (";
        foreach ($object as $key => $value) $sql .= "$key, ";
        $sql = substr($sql, 0, -2);
        $sql .= ") VALUES (";
        foreach ($object as $key => $value) $sql .= ":$key, ";
        $sql = substr($sql, 0, -2);
        $sql .= ") ON DUPLICATE KEY UPDATE ";
        foreach ($object as $key => $value) $sql .= "$key = :$key, ";
        $sql = substr($sql, 0, -2);

        $query = $this->db->prepare($sql);
        foreach ($object as $key => $value) $query->bindValue(":$key", $value);

        return $query->execute();
    }

    protected function doesItemExist($db_name, $item, $value)
    {
        $sql = "SELECT :item FROM $db_name WHERE :item = :value LIMIT 1";

        $query = $this->db->prepare($sql);
        $query->bindValue(':item', $item);
        $query->bindValue(':value', $value);
        $query->execute();

        $result_row = $query->fetchObject();

        return $result_row ? true : false;
    }
}