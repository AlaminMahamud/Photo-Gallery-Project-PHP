<?php

// it's going to need the database
// so probabily it would be smart enough to require it before we start
require_once(LIB_PATH.DS."database.php");

class User
{
    public $id;
    public $username;
    public $password;
    public $first_name;
    public $last_name;

    protected static $users_table_name = "users";
    protected static $id_column_name = "id";
    protected static $db_fields =  array('id', 'username', 'password', 'first_name', 'last_name' );

    private function has_attribute($attribute) {
        // get_object_vars returns an associative array with all attributes
        // (incl. private ones!) as the keys and their current values as the value
        $object_vars = get_object_vars($this);
        // We don't care about the value, we just want to know if the key exists
        // Will return true or false
        return array_key_exists($attribute, $object_vars);
    }
    private static function instantiate($record) {
        // Could check that $record exists and is an array
        $object = new self;
        // Simple, long-form approach:
        // $object->id 				= $record['id'];
        // $object->username 	= $record['username'];
        // $object->password 	= $record['password'];
        // $object->first_name = $record['first_name'];
        // $object->last_name 	= $record['last_name'];

        // More dynamic, short-form approach:
        foreach($record as $attribute=>$value){
            if($object->has_attribute($attribute)) {
                $object->$attribute = $value;
            }
        }
        return $object;
    }
    public static function find_all()
    {
        $sql  = "SELECT * FROM ";
        $sql .= self::$users_table_name;

        return self::find_by_sql($sql);
    }
    public static function find_by_id($id=0)
    {
        global $database;

        $sql  = "SELECT * FROM ";
        $sql .= self::$users_table_name;
        $sql .= " ";
        $sql .= "WHERE ";
        $sql .= self::$id_column_name;
        $sql .= "=";
        $sql .= $id;
        $sql .= " ";
        $sql .= "LIMIT ";
        $sql .= 1;

        $result_set = self::find_by_sql($sql);
        return !empty($result_set) ? array_shift($result_set):false;

    }
    public static function find_by_sql($sql = "")
    {
        global $database;
        $result_set = $database->query($sql);
        $object_array=array();
        while($row = $database->fetch_array($result_set))
        {
            $object_array[] = self::instantiate($row);
        }
        return $object_array;
    }
    public function full_name()
    {
        if(isset($this->first_name) && isset($this->last_name))
        {
            return $this->first_name." ".$this->last_name."<br/>";
        }else
            return "";
    }
    public static function authenticate($username="", $password="")
    {
        global $database;
        $username = $database->escape_value($username);
        $password = $database->escape_value($password);

        $sql = "SELECT * FROM ";
        $sql.= self::$users_table_name;
        $sql.= " ";
        $sql.= "WHERE username = '{$username}' ";
        $sql.= "AND password = '{$password}' ";
        $sql.= "LIMIT 1";

        $result_array = self::find_by_sql($sql);
        return !empty($result_array)?array_shift($result_array) : false;
    }
    public function attributes()
    {
        // return an array of attribute names and their values
        $attributes = array();
        foreach(self::$db_fields as $field)
        {
            if(property_exists($this, $field))
            {
                $attributes[$field] = $this->$field;
            }
            return $attributes;
        }
    }
    public function sanitized_attributes()
    {
        global $database;
        $clean_attributes = array();
        // sanitize the values before submitting
        // None: does not alter the actual value of each other
        foreach($this->attributes() as $key=>$value)
        {
            $clean_attributes[$key] = $database->escape_value($value);
        }
        return $clean_attributes;
    }
    public function save()
    {
        //  a new record wont have an id yet.
        return isset($this->id) ? $this->update() : $this->create();
    }
    public function create()
    {
        global $database;
        // DON'T forget your SQL Syntax and good habit
        // - INSERT INTO table(key,key) VALUES ('value','value'),
        // - single-quotes around all values
        // - escape all values to prevent SQL injection

        $attributes = $this->sanitized_attributes();
        $sql       = "INSERT INTO ";
        $sql      .= self::$users_table_name;
        $sql      .= " (";
        $sql      .= join(",", array_keys($attributes));
        $sql      .= " ) ";
        $sql      .= " VALUES";
        $sql      .= " ('";
        $sql      .= join("','", array_values($attributes));
        $sql      .= "')";

        if($database->query($sql))
        {
            $this->id = $database->insert_id();
            return true;
        }
        else
        {
            return false;
        }

    }
    public function update()
    {
        global $database;
        // Don't forget your SQL Syntax and good habits
        // - UPDATE table SET key = 'value' WHERE conditions
        // - single quote around all values
        // - escape all values to prevent SQL injection

        $attributes = $this->sanitized_attributes();
        $attributes_pairs  = array();
        foreach($attributes as $key => $value)
        {
            $attributes_pairs[] = "{$key} = '{$value}'";
        }

        $sql  = "UPDATE ";
        $sql .= self::$users_table_name;
        $sql .= " ";
        $sql .= "SET";
        $sql .= " ";
        $sql .= join(",", $attributes_pairs);
        $sql .= " ";
        $sql .= "WHERE id = ";
        $sql .= $database->escape_value($this->id);
        $database->query($sql);
        return ($database->affected_rows() == 1) ? true : false;
    }
    public function delete()
    {
        global $database;
        // Don't forget your SQL syntax and good habits;
        // - DELETE FROM table WHERE condition LIMIT 1
        // - escape all values to prevent SQL injection
        // - use LIMIT 1
        $sql = "DELETE FROM";
        $sql.= " ";
        $sql.= self::$users_table_name;
        $sql.= " ";
        $sql.= "WHERE ";
        $sql.= "id=";
        $sql.= $database->escape_value($this->id);
        $sql.= " ";
        $sql.= "LIMIT 1";
        $database->query($sql);
        return ($database->affected_rows()==1) ? true : false;

        // NB : After deleting, the instance of User still
        // exists, even though the database entry does not
        // this can be useful as in:
        //  echo $user->first_name . " was deleted";
        // but, for example, we can't call $user->update();
        // after calling $user->delete()
    }
}
?>