<?php

function connectToServer() {

    $servername = "localhost";
    $username = "root";
    $password = "";

    //create connection
    $conn = new mysqli($servername, $username, $password);
    //check conneciton
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function connectToDb($db_name) {

    $servername = "localhost";
    $username = "root";
    $password = "";

    //create connection
    $conn = new mysqli($servername, $username, $password, $db_name);
    //check conneciton
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function closeConnection($conn) {
    $conn->close();
}

function openDB($db_name) {

    // connect to server
    $conn = connectToServer();

    $sql = "CREATE DATABASE IF NOT EXISTS  {$db_name}";
    if ($conn->query($sql) !== TRUE) {
        die("Error creating database: " . $conn->error);
    }

    // connect to database
    $conn = connectToDb($db_name);

    return $conn;
}

function createTable($conn, $table_name, $fields_array, $indices_array) {

    $result = $conn->query("SHOW TABLES LIKE " . $table_name);

    if ($result === FALSE) {
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (";

        foreach ($fields_array as $field => $type) {
            if ($type === "") {
                $type = 'VARCHAR(255)';
            }
            $sql .= $field . "  " . $type . ",";
        }
        foreach ($indices_array as $index => $field) {
            $sql .= "{$field} ({$index}),";
        }
        $sql = rtrim($sql, ',');
        $sql .= ")";

        if ($conn->query($sql) === TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function getColumnsArray($table_name) {

    // returns array with column name as header and type as value
    $file_path = $GLOBALS['res_file'];
    $resource = $table_name . '_headers';
    $headers = getResourceFromXML($file_path, $resource, 'type');

    return $headers;
}

function getTables() {

    $conn = openDB('rwk_productchooserdb');
    $result = $conn->query("SELECT table_name FROM information_schema.tables where table_schema='rwk_productchooserdb'");

    $html = array();
    foreach ($result as $table) {
        if (stripos($table['table_name'], '_groups') === FALSE && stripos($table['table_name'], '_map') === FALSE) {
            $html[] = "<input id='{$table['table_name']}' class='button_class gen_table_btn'  type='button' name= '{$table['table_name']}' value='{$table['table_name']}' />";
        }
    }
    echo implode(' ', $html);
}

function insertRow($conn, $data, $table_name, $concat) {

    $exclude = array();

    foreach ($data as $key => $value) {
        if (!in_array($key, $exclude)) {

            $value = $conn->real_escape_string($value);
            $value = "'$value'";
            $assignments[] = "$key = $value";
        }
    }

    $insert_assignments = implode(",", $assignments);

    $insert = "INSERT INTO $table_name SET $insert_assignments ";
    $duplicate = "ON DUPLICATE KEY ";
    $update = "UPDATE $insert_assignments";
    if ($concat) {
        $if_id = "IF (INSTR(Variant_IDs, '{$data['Variant_IDs']}') > 0, Variant_IDs, CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}'))";
        $if_colour = "IF (INSTR(Colour, '{$data['Colour']}') > 0, Colour, CONCAT_WS(',', Colour, '{$data['Colour']}'))";
        $if_size = "IF (INSTR(Size, '{$data['Size']}') > 0, Size, CONCAT_WS(',', Size, '{$data['Size']}'))";
        $update = "UPDATE SKU = SKU, Name = Name, Variant_IDs = $if_id, Colour = $if_colour, Size = $if_size";
//        $if = "IF (INSTR(Variant_IDs, '{$data['Variant_IDs']}') > 0, Variant_IDs, CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}', Colour, '{$data['Colour']}', Size, '{$data['Size']}'))";
//        $update = "UPDATE SKU = SKU, Name = Name, Variant_IDs = $if";
//        $update = "UPDATE Base_SKU = Base_SKU, Variant_IDs = CONCAT_WS(',', Variant_IDs, '{$data['Variant_IDs']}')";
    }

    $sql = $insert . $duplicate . $update;
    if ($conn->query($sql)) {
        return TRUE;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function mysql_insert_array($table, $data, $exclude = array()) {

    $fields = $values = array();
    if (!is_array($exclude))
        $exclude = array($exclude);
    foreach (array_keys($data) as $key) {
        if (!in_array($key, $exclude)) {
            $fields[] = "`$key`";
            $values[] = "'" . mysql_real_escape_string($data[$key]) . "'";
        }
    }
    $fields = implode(",", $fields);
    $values = implode(",", $values);
    if (mysql_query("INSERT INTO `$table` ($fields) VALUES ($values)")) {
        return array("mysql_error" => false,
            "mysql_insert_id" => mysql_insert_id(),
            "mysql_affected_rows" => mysql_affected_rows(),
            "mysql_info" => mysql_info()
        );
    } else {
        return array("mysql_error" => mysql_error());
    }
}

function insertData($conn, $data, $table) {

    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $value = $conn->real_escape_string($value);
            $value = "'$value'";
            $assignments[] = "$key = $value";
        }

        $insert = implode(',', $assignments);
        $sql = "INSERT INTO $table SET" . $insert;
    }

    if ($conn->query($sql)) {
        return TRUE;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function getRow($conn, $table_name, $row) {

    $result = $conn->query("SELECT * FROM {$table_name} LIMIT {$row}, 1");
//    $data = $result->fetch_row();
    $data = $result->fetch_assoc();

    if ($data != null) {
        return $data;
    } else {
        return FALSE;
    }
}

function getProductByID($table_name, $ID) {

    $conn = openDB('rwk_productchooserdb');
    $result = $conn->query("SELECT * FROM {$table_name} WHERE Product_ID IN ({$ID})");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductData($conn, $table_name, $group_id, $items_per_page, $filter, $previous_page = FALSE) {

    // for $start_row read group_id
    $product_count = 0;

    $sql_str = "SELECT * FROM {$table_name}";
    if ($filter !== FALSE AND $filter !== 'All') {
        $sql_str .= " WHERE {$filter})";
    }

    $results = $conn->query($sql_str);

    if ($results !== FALSE) {
        foreach ($results as $row) {
            $product_count ++;

            $array[] = [
                'Selling' => 0,
                'Product_ID' => $row['Product_ID'],
                'Name' => $row['Name'],
                'SKU' => $row['SKU'],
                'Price_RRP' => $row['Price_RRP'],
                'Trade_Price' => $row['Trade_Price'],
                'Description' => $row['Description'],
                'Image' => $row['Image'],
                'Colour' => $row['Colour'],
                'Size' => $row['Size']
            ];

//            $grouped_variants[$row['Product_ID']] = [
//                'Selling' => 0,
//                'Product_ID' => $row['Product_ID'],
//                'Name' => $row['Name'],
//                'SKU' => $row['SKU'],
//                'Price_RRP' => $row['Price_RRP'],
//                'Trade_Price' => $row['Trade_Price'],
//                'Description' => $row['Description'],
//                'Image' => $row['Image'],
//                'Colour' => $row['Colour'],
//                'Size' => $row['Size']
//            ];
//
//            $array[] = $grouped_variants;
//            unset($grouped_variants);
            $current_row = $product_count + $items_per_page;

            if ($product_count >= $items_per_page) {
                $_POST['current_row'] = (string) $current_row;
                break;
            }
        }
        return $array;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function getProductData_OLD($conn, $table_name, $start_row, $items_per_page, $filter) {

    $product_count = 0;
    $table = $table_name . '_groups';
    $groups_results = $conn->query("SELECT * FROM {$table} LIMIT {$start_row}, {$items_per_page}");

    if ($groups_results !== FALSE) {

        $table = $table_name . '_variants';

        foreach ($groups_results as $group_row) {
            $variants[] = $group_row['Variant_IDs'];
            $sql_str = "SELECT * FROM {$table} WHERE Product_ID IN ({$group_row['Variant_IDs']}";

            if ($filter !== FALSE AND $filter !== 'All') {
                $sql_str .= "AND {$filter})";
            } else {
                $sql_str .= ")";
            }
            $variant_results = $conn->query($sql_str);

            if ($variant_results !== FALSE) {
                $product_count ++;
                foreach ($variant_results as $variant_row) {
                    $grouped_variants[$variant_row['Product_ID']] = [
                        'Selling' => $variant_row['Selling'],
                        'Product_ID' => $variant_row['Product_ID'],
                        'Name' => $variant_row['Name'],
                        'SKU' => $variant_row['SKU'],
                        'Price_RRP' => $variant_row['Price_RRP'],
                        'Trade_Price' => $variant_row['Trade_Price'],
                        'Description' => $variant_row['Description'],
                        'Image' => $variant_row['Image'],
                        'Colour' => $variant_row['Colour'],
                        'Size' => $variant_row['Size']
                    ];
                }
                $array[] = $grouped_variants;
                unset($grouped_variants);
            }
        }
        return $array;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function updateSellingDB($conn, $table_name, $selling_list) {

    $checkbox = json_decode($selling_list, TRUE);
    $update = "UPDATE {$table_name} SET Selling = {$checkbox['checked']} WHERE Product_ID = {$checkbox['id']}";

    $sql = $update;
    if ($conn->query($sql)) {
        return TRUE;
    } else {
        return array("mysqli_error" => $conn->error);
    }
}

function get_largest_id($table_name) {

    $conn = openDB('rwk_productchooserdb');
    $result = $conn->query("SELECT MAX(Product_ID) AS max_id FROM $table_name");
    $row = $result->fetch_assoc();
    return $row['max_id'];
}

function skuExists($sku, $table) {

    $conn = openDB('rwk_productchooserdb');
    return $conn->query("SELECT Group_ID FROM {$table} WHERE SKU = {$sku}");
}

function bulkFillTable($conn, $file) {

    $table = str_replace(".csv", "", $file);
    $fields_array = getCSVHeaders($file);

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (";
    $type = 'VARCHAR(255)';

    foreach ($fields_array as $field) {
        $field = str_replace(' ', '_', $field);
        $field = str_replace('(', '', $field);
        $field = str_replace(')', '', $field);
        $field = str_replace(',', '', $field);
        $sql .= $field . "  " . $type . ",";
    }

    $sql = rtrim($sql, ',');
    $sql .= ")";

    if ($conn->query($sql) !== TRUE) {
        return FALSE;
    }

    $file = "C:/xampp/htdocs/ImagesFromCSV/resources/" . $file;
    $sql = "LOAD DATA INFILE '{$file}' INTO TABLE {$table} FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES";

    $result = $conn->query($sql);
    if ($result !== TRUE) {
        return array("mysqli_error" => $conn->error);
    }
    
    $sql = "ALTER TABLE {$table} ADD Selling BOOLEAN FIRST, ADD Parent INT(10) UNSIGNED FIRST";
    
    $result = $conn->query($sql);
    if ($result !== TRUE) {
        return array("mysqli_error" => $conn->error);
    }
}

function createGroupsTable($conn, $file_name) {

    $table = str_replace(".csv", "_groups", $file_name);

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (Group_ID INT(10) PRIMARY KEY, Base_SKU VARCHAR(255), Variant_IDs VARCHAR(255))";

    if ($conn->query($sql) === TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function populateGroupsTable($conn, $file_name) {
// and update image url and parent id

    $table = str_replace(".csv", "", $file_name);
    $groups_array = [];
    $group_id = 0;
    $sql = "SELECT Product_ID, SKU, Image FROM {$table}";

    $results = $conn->query($sql);
    if ($results !== FALSE) {

        while ($row = $results->fetch_assoc()) {       
            $baseSKU = getBaseSKU($row['SKU']);
            if (array_key_exists($baseSKU, $groups_array)) {
                $groups_array[$baseSKU]['variants'] = $groups_array[$baseSKU]['variants'] . ', ' . $row['Product_ID'];
                $group_id = $groups_array[$baseSKU]["group_id"];
            } else {
//                $groups_array[$baseSKU] = $row['Product_ID'];         
                $groups_array[$baseSKU] = array("group_id" => count($groups_array) + 1, "variants" => $row['Product_ID']);
                $group_id = $groups_array[$baseSKU]["group_id"];
            }
            
            $image = getImageFromWeb($row['Image']);
            $image = "'$image'";
//            $sql = "UPDATE {$table} SET Image = {$image}  WHERE Product_ID ={$row['Product_ID']}";
            $sql = "UPDATE {$table} SET Image = {$image}, Parent = {$group_id}  WHERE Product_ID ={$row['Product_ID']}";
            $sql_result = $conn->query($sql);

            if ($sql_result !== TRUE) {
                return array("mysqli_error" => $conn->error);
            }
        }

        $table = $table . "_groups";
        foreach ($groups_array as $sku => $values) {
            $sku = "'$sku'";
            $variants = $values['variants'];
            $variants = "'$variants'";
            $group_id = $values['group_id'];
            $group_id = "'$group_id'";

            // NULL auto_increments the Group_ID
            $insert = $group_id . ", " . $sku . ", " . $variants;
            $sql = "INSERT INTO $table (Group_ID, Base_SKU, Variant_IDs)  VALUES (" . $insert . ")";
            if ($conn->query($sql) !== TRUE) {
                return FALSE;
            }
        }
    } else {
        return array("mysqli_error" => $conn->error);
    }
}
