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
                    'success' => true,
                    'result' => $result,
                    'message' => 'Property Listed Sucessfully.'
                ];
            } else {
                array_push($this->result, $stmt->error);
                return [
                    'success' => false,
                    'result' => $result,
                    'error' => 'something went wrong',
                    'message' => 'Property Not Listed!'
                ];
            }
        } else {
            array_push($this->result, "Table 'properties' does not exist");
            return [
                'success' => false,
                'error' => 'Something went wrong.',
                'message' => 'Table not Exist'
            ];
        }
    }

    public function getProperties(int $page = 1, int $limit = 10): array
    {
        $result = [
            "success" => false,
            "message" => "",
            "data" => [],
            "pagination" => []
        ];

        // check if property table exists
        if (!$this->table_exist("property")) {
            $result["message"] = "Properties table does not exist";
            return $result;
        }

        // calculate offset safely
        $page = max(1, $page); // avoid negative/zero page
        $offset = ($page - 1) * $limit;

        // 1. count total properties
        $countQuery = "SELECT COUNT(*) as total FROM property";
        $countResult = $this->database->query($countQuery);

        if ($countResult === false) {
            $result["message"] = "Error fetching total count: " . $this->database->error;
            return $result;
        }

        $row = $countResult->fetch_assoc();
        $total_records = (int) ($row['total'] ?? 0);

        // 2. fetch paginated properties with user name
        $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) AS listed_by
          FROM property p 
          LEFT JOIN users u ON p.listed_by = u.id 
          ORDER BY p.id DESC 
          LIMIT ? OFFSET ?";

        $stmt = $this->database->prepare($query);

        if (!$stmt) {
            $result["message"] = "Prepare failed: " . $this->database->error;
            return $result;
        }

        $stmt->bind_param("ii", $limit, $offset);

        if (!$stmt->execute()) {
            $result["message"] = "Execute failed: " . $stmt->error;
            return $result;
        }

        $properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3. set response
        $result["success"] = true;
        $result["message"] = $properties ? "Properties fetched successfully" : "No properties found";
        $result["data"] = $properties;

        // pagination info
        $total_pages = ($limit > 0) ? ceil($total_records / $limit) : 1;
        $result["pagination"] = [
            "current_page" => $page,
            "per_page" => $limit,
            "total_records" => $total_records,
            "total_pages" => $total_pages,
            "has_next" => $page < $total_pages,
            "has_prev" => $page > 1
        ];
        return $result;
    }

    // get all properties
    public function getallProperties(?string $type = null, ?string $category = null, ?string $name = null): array
    {
        $result = [
            "success" => false,
            "message" => "",
            "data" => [],
            "pagination" => []
        ];

        // check if property table exists
        if (!$this->table_exist("property")) {
            $result["message"] = "Properties table does not exist";
            return $result;
        }

        // base query
        $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) AS listed_by
              FROM property p
              LEFT JOIN users u ON p.listed_by = u.id";

        // conditions array
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($type)) {
            $conditions[] = "p.type = ?";
            $params[] = $type;
            $types .= "s";
        }

        if (!empty($category)) {
            $conditions[] = "p.category = ?";
            $params[] = $category;
            $types .= "s";
        }

        if (!empty($name)) {
            $conditions[] = "p.name LIKE ?";
            $params[] = "%" . $name . "%";
            $types .= "s";
        }

        // add WHERE if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // order by latest
        $query .= " ORDER BY p.id DESC";

        $stmt = $this->database->prepare($query);
        if (!$stmt) {
            $result["message"] = "Prepare failed: " . $this->database->error;
            return $result;
        }

        // bind params if filters are provided
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $result["message"] = "Execute failed: " . $stmt->error;
            return $result;
        }

        $properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // response
        $result["success"] = true;
        $result["message"] = $properties ? "Properties fetched successfully" : "No properties found";
        $result["data"] = $properties;

        return $result;
    }

    // update the property
    public function updateProperty(int $id, array $params = []): array
    {
        echo "<pre>";
        var_dump($params);
        echo "</pre>";
        if ($this->table_exist('property')) {
            if (empty($params)) {
                return [
                    'success' => false,
                    'message' => 'No data provided for update.'
                ];
            }

            // build SET clause dynamically: column1 = ?, column2 = ?
            $set_clause = implode(", ", array_map(function ($col) {
                return "$col = ?";
            }, array_keys($params)));

            $query = "UPDATE property SET $set_clause WHERE id = ?";
            $stmt = $this->database->prepare($query);

            if ($stmt === false) {
                return [
                    'success' => false,
                    'error' => $this->database->error,
                    'message' => 'Failed to prepare statement'
                ];
            }

            // build types (all strings + one integer for id at the end)
            $types = str_repeat("s", count($params)) . "i";
            $values = array_values($params);
            $values[] = $id; // add id at the end for WHERE clause

            // bind params dynamically
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Property updated successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $stmt->error,
                    'message' => 'Failed to update property.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => "Table 'property' does not exist"
            ];
        }
    }

    // get a property details
    public function get_single_property(int $id): array
    {
        $result = [
            "success" => false,
            "message" => "",
            "data" => [],
            "pagination" => []
        ];

        // check if property table exists
        if (!$this->table_exist("property")) {
            $result["message"] = "Properties table does not exist";
            return $result;
        }

        // 2. fetch properties with user name
        $query = "SELECT p.*, CONCAT(u.firstname, ' ', u.lastname) AS listed_by
                FROM property p
                LEFT JOIN users u ON p.listed_by = u.id
                WHERE p.id = ?
                ORDER BY p.id DESC
                ";

        $stmt = $this->database->prepare($query);

        if (!$stmt) {
            $result["message"] = "Prepare failed: " . $this->database->error;
            return $result;
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $result["message"] = "Execute failed: " . $stmt->error;
            return $result;
        }

        $properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 3. set response
        $result["success"] = true;
        $result["message"] = $properties ? "Properties fetched successfully" : "No properties found";
        $result["data"] = $properties;

        return $result;
    }

    // getting class deleted
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