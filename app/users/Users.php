<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
use Phalcon\Mvc\Model;

class Users extends Model
{
    private $_property = [];

    public function save() : bool
    {
        $result = true;
        $name     = $this->username;
        $password = $this->password;
        $role     = $this->role;
        $row = Nodes::count(Nodes::PREFIX . "model = 'users' and " . Nodes::PREFIX . "name = 'id'");
        $id = $row + 1;
        if ($result) {
            $result = $this->insert_node($row, 'id', $id);
        }
        if ($result) {
            $result = $this->insert_node($row, 'name', $name);
        }
        if ($result) {
            $result = $this->insert_node($row, 'password', $password);
        }
        if ($result) {
            $result = $this->insert_node($row, 'role', $role);
        }
        return $result;
    }
    public function __set($name, $value)
    {
        $this->_property[$name] = $value;
    }
    public function __get($name)
    {
        if (isset($this->_property[$name])) {
            return $this->_property[$name];
        } else {
            return null;
        }
    }
    private function insert_node($row, $name, $value)
    {
        $node = new Nodes();
        $node->model = 'users';
        $node->name  = $name;
        $node->row   = $row;
        $node->value = $value;
        return $node->save();
    }
}
