<?php
/**
 * Repo OHM: Desmos Graphs
 */

namespace Desmos\Models;
use PDO;

/**
 * Class DesmosItem
 *
 * @package Desmos\Models
 * @author  Alena Holligan <alena@lumenlearning.com>
 */
class DesmosGraph
{

    /**
     * Find desmos graph by id
     *
     * @param int $id Autoincrement ID for the graph json
     *
     * @return string json
     */
    public static function findGraph(int $id)
    {
        $query = "SELECT data FROM desmos_graphs WHERE id = :id";
        $stm = $stm = $GLOBALS['DBH']->prepare($query);
        $stm->bindValue(':id', $id);
        $stm->execute();
        return $stm->fetchColumn();
    }
}