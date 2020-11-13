<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
use Phalcon\Mvc\Model;

class Nodes extends Model
{
    const PREFIX = 'node_';

    private $node_id;
    private $node_model;
    private $node_name;
    private $node_row;
    private $node_value;
    private $node_active;
    private $node_creator;
    private $node_time;

    public function __set($name, $value)
    {
        $property = self::PREFIX . $name;
        $this->$property = $value;
    }
    public function __get($name)
    {
        $property = self::PREFIX . $name;
        if (isset($this->$property)) {
            return $this->$property;
        } else {
            return null;
        }
    }
}
