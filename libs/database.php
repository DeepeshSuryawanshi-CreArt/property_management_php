<?php
class database
{
    private string $db_host = 'localhost';
    private string $db_username = 'root';
    private string $db_password = '';
    private string $db_database = 'real_estate';
    private $conn = false;
    private $database = '';
    public $result = array();
    public function __construct()
    {
        if (!$this->conn) {
            $this->database = new mysqli(hostname: $this->db_host, username: $this->db_username, password: $this->db_password, database: $this->db_database);
            if ($this->database->connect_error) {
                array_push($this->result, $this->database->connect_error);
            }
            $this->conn = true;
        }
    }
    // login
    public function login(string $user_email, string $password): array
    {
        $result = $this->select_user("WHERE email = '$user_email' ");
        if ($result["success"]) {
            $user = $result['user'];
            if (password_verify($password, $user['password'])) {
                return [
                    'success' => true,
                    'message' => 'User Login Sucessfully',
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid or wrong Password',
                    'user' => $user
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'User Not Found With this Email',
                'user' => null
            ];
        }
    }
    //register
    public function register($table, $params = array()): bool
    {
        if ($this->table_exist($table)) {
            // check email in unique;
            $checkQuery = "SELECT id FROM users WHERE email = ?";
            $stmt = $this->database->prepare($checkQuery);
            $stmt->bind_param("s", $params['email']);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Email already exists
                array_push($this->result, "Email already registered!");
                return false;
            }
            // column names
            $columns = implode(", ", array_keys($params));
            // placeholders (?, ?, ?)
            $placeholders = implode(", ", array_fill(0, count($params), "?"));

            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->database->prepare($query);

            if ($stmt === false) {
                array_push($this->result, $this->database->error);
                return false;
            }

            // dynamic types string (all 's' = string, but you can improve later)
            $types = str_repeat("s", count($params));
            $values = array_values($params);

            // bind_param requires unpacking
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();
            if ($result) {
                array_push($this->result, $this->database->insert_id);
                return true;
            } else {
                array_push($this->result, $stmt->error);
                return false;
            }
        } else {
            return false;
        }
    }
    // select 
    public function select_user($condition)
    {
        $table = 'users';
        if ($this->table_exist($table)) {
            $sql = "SELECT * FROM $table $condition";
            $result = $this->database->query($sql);
            if ($result->num_rows == 1) {
                return [
                    'success' => true,
                    'user' => $result->fetch_assoc(),
                ];
            } else {
                return [
                    'success' => false,
                    'user' => $result,
                ];
            }
        } else {
            array_push($this->result, "Table not exist in the Database : " . $table);
        }
    }

    // PROPERTY SYSTEM WORKING.
    // Add property
    public function addProperty($params = array()): array
    {
        if ($this->table_exist('property')) {
            // column names
            $columns = implode(", ", array_keys($params));
            // placeholders (?, ?, ?)
            $placeholders = implode(", ", array_fill(0, count($params), "?"));

            $query = "INSERT INTO property ($columns) VALUES ($placeholders)";
            $stmt = $this->database->prepare($query);

            if ($stmt === false) {
                array_push($this->result, $this->database->error);
                return false;
            }

            // build types string dynamically
            $types = str_repeat("s", count($params));
            $values = array_values($params);

            // bind params
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();

            if ($result) {
                array_push($this->result, $this->database->insert_id); // return inserted property_id
                return [
                    'success' => false,
                    'result' => $result,
                    'message' => 'Property Listed Sucessfully.'
                ];
            } else {
                array_push($this->result, $stmt->error);
                return [
                    'success' => false,
                    'result' => $result,
                    'error'=>'something went wrong',
                    'message' => 'Property Not Listed!'
                ];
            }
        } else {
            array_push($this->result, "Table 'properties' does not exist");
            return [
                'success' => false,
                'error'=>'Something went wrong.',
                'message' => 'Table not Exist'
            ];
        }
    }


    public function __destruct()
    {
        if ($this->conn) {
            if ($this->database->close()) {
                $this->conn = false;
                $this->database = '';
            }
        }
    }

    // helper functions 
    private function table_exist($table): bool
    {
        $sql = "SHOW TABLES FROM $this->db_database LIKE '$table' ";
        $get_table_result = $this->database->query($sql);
        if ($get_table_result->num_rows == 1) {
            return true;
        } else {
            array_push($this->result, $table . "Is Not Exist In Database.");
            return false;
        }
    }
    public function get_result()
    {
        $value = $this->result;
        $this->result = array();
        return $value;
    }
}