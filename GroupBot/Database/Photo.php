<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 22/02/2016
 * Time: 7:39 PM
 */

namespace GroupBot\Database;


class Photo extends DbConnection
{
    public function addServerPhotoId($file_id, $md5, $local_path)
    {
        $sql = 'INSERT INTO photos (file_id, md5, local_path)
                VALUES (:file_id, :md5, :local_path)';

        $query = $this->db->prepare($sql);
        $query->bindValue(':file_id', $file_id);
        $query->bindValue(':md5', $md5);
        $query->bindValue(':local_path', $local_path);

        return $query->execute();
    }

    public function getServerPhotoId($md5, $local_path)
    {
        $sql = 'SELECT COUNT(*)
                FROM photos
                WHERE md5 = :md5';

        $query = $this->db->prepare($sql);
        $query->bindValue(':md5', $md5);
        $query->execute();
        $no_rows = $query->fetchColumn();

        if ($no_rows > 0) {
            $sql = 'SELECT file_id, local_path
                FROM photos
                WHERE md5 = :md5';

            $query = $this->db->prepare($sql);
            $query->bindValue(':md5', $md5);
            $query->execute();

            if ($no_rows == 1) return $query->fetch()['file_id'];

            $results = $query->fetchAll();
            foreach ($results as $item) {
                if ($item['local_path'] == $local_path) return $item['file_id'];
            }
        }
        return false;
    }

    public function getRadarPhotoId($local_path)
    {
        $sql = 'SELECT file_id
                FROM photos
                WHERE local_path = :local_path';

        $query = $this->db->prepare($sql);
        $query->bindValue(':local_path', $local_path);
        $query->execute();

        if ($query->rowCount()) {
            return $query->fetch()['file_id'];
        }
        return false;

        $no_rows = $query->fetchColumn();

        if ($no_rows == 1) return $query->fetch()['file_id'];
        return false;
    }

}