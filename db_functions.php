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

function getTables() {

        $conn = openDB('rwk_productchooserdb');
        $result = $conn->query("SELECT table_name FROM information_schema.tables where table_schema='rwk_productchooserdb'");

        $html = array();
        foreach ($result as $table) {
                if (stripos($table['table_name'], '_groups') === FALSE && stripos($table['table_name'], '_map') === FALSE && stripos($table['table_name'], '_brands') === FALSE && stripos($table['table_name'], '_categories') === FALSE) {
                        $html[] = "<input id='{$table['table_name']}' class='button_class gen_table_btn'  type='button' name= '{$table['table_name']}' value='{$table['table_name']}' />";
                }
        }
        echo implode(' ', $html);
}

function getProductsByID($conn, $table_name, $IDs) {

        $result = $conn->query("SELECT * FROM {$table_name} WHERE Product_ID IN ({$IDs})");

        return $result->fetch_all(MYSQLI_ASSOC);
}

function getBrands($table_name) {

        $conn = openDB('rwk_productchooserdb');
        $sql = "SELECT * FROM {$table_name}_brands";
        $results = $conn->query($sql);
        $error = $conn->error;
        $conn->close();

        if ($results !== FALSE) {
                return $results;
        } else {
                return FALSE;
        }
}

function getCategories($table_name) {

        $conn = openDB('rwk_productchooserdb');
        $sql = "SELECT * FROM {$table_name}_categories";
        $results = $conn->query($sql);
        $error = $conn->error;
        $conn->close();

        if ($results !== FALSE) {
                return $results;
        } else {
                return FALSE;
        }
}

function getGroupedProductData($conn, $table_name, $start_row, $items_per_page, $filters, $previous_page = FALSE) {

        $sql = "SELECT Group_ID, Product_IDs FROM {$table_name}_groups";
        $sql .= " LIMIT {$start_row}, 1000000";
        $group_results = $conn->query($sql);

        if ($group_results !== FALSE) {
                $start_group = 0;
                $num_groups = 0;
                foreach ($group_results as $group_row) {

                        if ($num_groups >= $items_per_page) {
                                return $array;
                        }
                        $image_group = "";

                        $sql = "SELECT * FROM {$table_name} WHERE Product_ID IN ({$group_row['Product_IDs']})";

                        if ($filters[0] !== FALSE AND $filters[0] !== 'All') {
                                $filter_groups = [];
                                foreach ($filters as $filter) {
                                        $pos = strpos($filter, "=");
                                        $lhs = substr($filter, 0, $pos);
                                        $lhs = trim($lhs);
                                        $rhs = substr($filter, $pos + 1);
                                        $rhs = trim($rhs);
//                                        $rhs = "'$rhs'";

                                        if (array_key_exists($lhs, $filter_groups)) {
                                                $filter_groups[$lhs] = $filter_groups[$lhs] . ',' . $rhs;
                                        } else {
                                                $filter_groups[$lhs] = $rhs;
                                        }
                                }
                                $sql .= " AND ";
                                foreach ($filter_groups as $key => $filter) {
                                        if ($key === 'Brand' && $filter === 'All'){
                                                continue;
                                        }
                                         if ($key === 'Category'){
                                                $sql .= "INSTR('{$key}', '{$filter}') > 0";
                                                continue;
                                        }
                                        $sql .= "{$key} IN ('{$filter}')";
                                        $sql .= " AND ";
                                }
                                $sql = rtrim($sql, 'AND ');
                        }

                        $product_results = $conn->query($sql);

                        if ($product_results !== FALSE && $product_results->num_rows !== 0) {
                                $num_groups++;
                                foreach ($product_results as $product) {
                                        $image_array_in = explode(',', $product['Image']);
                                        $image_array_out = [];
                                        $update = FALSE;
                                        $index = 0;

                                        foreach ($image_array_in as $url) {
                                                if (substr($url, 0, 8) !== './media/') {
                                                        $url = getImage($url, $product['Brand'], $product['SKU'], $index);
                                                        // need to update url in database
                                                        $update = TRUE;
                                                }
                                                $index++;
                                                $image_array_out[] = $url;
                                        }

                                        $images = implode(',', $image_array_out);
                                        $image_group .= ',' . $images;

                                        if ($update) {
                                                $result = updateImageField($conn, $table_name, $images, $product['Product_ID']);
                                        }

                                        $grouped_array[] = [
                                            'Selling' => $product['Selling'],
                                            'Product_ID' => $product['Product_ID'],
                                            'Name' => $product['Name'],
                                            'SKU' => $product['SKU'],
                                            'Price_RRP' => $product['Price_RRP'],
                                            'Trade_Price' => $product['Trade_Price'],
                                            'Description' => $product['Description'],
                                            'Image' => $images,
                                            'Colour' => $product['Colour'],
                                            'Size' => $product['Size'],
                                            'Stock_Type' => $product['Stock_Type'],
                                            'Stock_Level' => $product['Stock_Level'],
                                            'Brand' => $product['Brand']
                                        ];
                                }
                                $image_group = ltrim($image_group, ',');
                                $array[] = array('group_id' => $group_row['Group_ID'], 'images' => $image_group, 'products' => $grouped_array);
                                unset($grouped_array);
                        }
                }
                return $array;
        } else {
                return array("mysqli_error" => $conn->error);
        }
}

function updateImageField($conn, $table_name, $imageString, $id) {

        $update = "UPDATE {$table_name} SET Image = '{$imageString}' WHERE Product_ID = {$id}";

        $sql = $update;
        if ($conn->query($sql)) {
                return TRUE;
        } else {
                return array("mysqli_error" => $conn->error);
        }
}

function updateSelling() {

        if (!isset($_SESSION)) {
                session_start();
        }
        $table_name = $_SESSION['table_name'];
        $conn = openDB('rwk_productchooserdb');

        $checkbox = json_decode($_POST['selling'], TRUE);
        $selling = $checkbox['checked'] ? 'TRUE' : 'FALSE';
        $sql = "UPDATE {$table_name} SET Selling = {$selling} WHERE Product_ID = {$checkbox['id']}";

        if ($conn->query($sql)) {
                $conn->close();
                return TRUE;
        } else {
                $error = $conn->error;
                $conn->close();
                return array("mysqli_error" => $error);
        }
}

function getLargestID($table_name) {

        $conn = openDB('rwk_productchooserdb');
        $result = $conn->query("SELECT MAX(Product_ID) AS max_id FROM $table_name");
        $row = $result->fetch_assoc();
        return $row['max_id'];
}

function bulkFillTable($conn, $file_name) {

        $table = str_replace(".csv", "", $file_name);

        $_SESSION['table_name'] = $table;

        $sql = "DROP TABLE IF EXISTS {$table}";
        if ($conn->query($sql) === FALSE) {
                return array("mysqli_error" => $conn->error);
        }

        $fields_array = getCSVHeaders($file_name);

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
                return array("mysqli_error" => $conn->error);
        }

        $file_name = "C:/wamp64/www/ImagesFromCSV/resources/" . $file_name;
        // get number of rows in file
        $file = new SplFileObject($file_name, 'r');
        $file->seek(PHP_INT_MAX);
        $num_rows_in_file = $file->key() + 1;

        $sql = "SELECT COUNT(*) FROM {$table}";
        if ($field != NULL) {
                $sql .= " GROUP BY {$field}";
        }
        $result = $conn->query($sql);
        if ($result === FALSE) {
                return array("mysqli_error" => $conn->error);
        } else {
                $num_rows_in_table = mysqli_num_rows($result);
        }

        if ($num_rows_in_table !== 0) {
                $sql = "TRUNCATE {$table}";
                if ($conn->query($sql) === FALSE) {
                        return array("mysqli_error" => $conn->error);
                }
        }

//    $sql = "LOAD DATA INFILE '{$file_name}' INTO TABLE {$table} CHARACTER SET UTF8 FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
        $sql = "LOAD DATA LOCAL INFILE '{$file_name}' INTO TABLE {$table} FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES";

        $result = $conn->query($sql);
        if ($result !== TRUE) {
                return array("mysqli_error" => $conn->error);
        }
        // "ALTER TABLE `alterego_current_stockline_green` ADD PRIMARY KEY(`Product_ID`);"
        $sql = "ALTER TABLE {$table} ADD Selling BOOLEAN FIRST, ADD Parent VARCHAR(255), ADD Local_Images VARCHAR(255), ADD Local_SKU VARCHAR(255) FIRST, ADD PRIMARY KEY(`Product_ID`)";

        $result = $conn->query($sql);
        if ($result !== TRUE) {
                return array("mysqli_error" => $conn->error);
        }
}

function createGroupsTable($conn, $file_name) {

        $table = str_replace(".csv", "_groups", $file_name);

        $sql = "DROP TABLE IF EXISTS {$table}";
        if ($conn->query($sql) === FALSE) {
                return array("mysqli_error" => $conn->error);
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (Group_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Parent VARCHAR(255), Product_IDs VARCHAR(255), Image VARCHAR(255))";

        if ($conn->query($sql) === TRUE) {
                return TRUE;
        } else {
                return FALSE;
        }
}

function createBrandsTable($conn, $file_name) {

        $table = str_replace(".csv", "_brands", $file_name);

        $sql = "DROP TABLE IF EXISTS {$table}";
        if ($conn->query($sql) === FALSE) {
                return array("mysqli_error" => $conn->error);
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (Brand_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Brand VARCHAR(255) UNIQUE)";

        if ($conn->query($sql) === TRUE) {
                return TRUE;
        } else {
                return FALSE;
        }
}

function createCategoriesTable($conn, $file_name) {

        $table = str_replace(".csv", "_categories", $file_name);

        $sql = "DROP TABLE IF EXISTS {$table}";
        if ($conn->query($sql) === FALSE) {
                return array("mysqli_error" => $conn->error);
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (Category_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Category VARCHAR(255) UNIQUE)";

        if ($conn->query($sql) === TRUE) {
                return TRUE;
        } else {
                return FALSE;
        }
}

function reformatMainTable($conn, $file_name) {

        $table = str_replace(".csv", "", $file_name);
        $groups_array = [];
        $brands_array = [];
        $categories_array = [];
        $fields = 'Product_ID, SKU, Name, Product_Range, Brand, Categories, Image, Image_1, Image_2, Image_3, Image_4, Image_5, Image_6, Image_7, Image_8, Image_9, Image_10';
        $sql = "SELECT  {$fields} FROM {$table}";

        $results = $conn->query($sql);
        if ($results !== FALSE) {

                while ($row = $results->fetch_assoc()) {

                        if ($row['Product_Range'] === "") {
                                $ParentSKU = $row['Name'];
                        } else {
                                $ParentSKU = $row['Product_Range'];
                        }
                        $ParentSKU = generateParentSKU($ParentSKU);
                        $Product_ID = $row['Product_ID'];

                        if (!empty($row['Brand'])) {
                                $brand = $row['Brand'];
                                $brand = "'$brand'";

                                if (!in_array("({$brand})", $brands_array)) {
                                        $brands_array[] = "({$brand})";
                                }
                        }

                        if (!empty($row['Categories'])) {
                                $array = splitCats($row['Categories']);
                                foreach ($array as $cat) {
                                        $cat = "'$cat'";
                                        if (!in_array("({$cat})", $categories_array)) {
                                                $categories_array[] = "({$cat})";
                                        }
                                }
                        }
                        $image_array = createImageArray($row);
                        $image_string = implode(',', $image_array);
                        $image_string = rtrim($image_string, ', ');
//                        $image_string = "'$image_string'";
//                        $groups_array[] = "({$ParentSKU},{$Product_IDs},{$image_string})";
                        if (key_exists($ParentSKU, $groups_array)) {
                                $groups_array[$ParentSKU]['Product_IDs'] .= ',' . $Product_ID;
                        } else {
                                $groups_array[$ParentSKU] = array('Product_IDs' => $Product_ID, 'Image_string' => $image_string);
                        }
                }
                $values = "";
                foreach ($groups_array as $key => $group_array) {
                        $ParentSKU = "'$key'";
                        $Product_IDs = $group_array['Product_IDs'];
                        $Product_IDs = "'$Product_IDs'";
                        $image_string = $group_array['Image_string'];
                        $image_string = "'$image_string'";

                        $values = $values . ',' . "({$ParentSKU},{$Product_IDs},{$image_string})";
                }

                $values = ltrim($values, ',');
                $group_table = $table . "_groups";
//                $values = implode(',', $groups_array);
                $sql = "INSERT INTO {$group_table} (Parent, Product_IDs, Image) VALUES {$values}"; // change Product_Range to Parent_SKU
                $sql_result = $conn->query($sql);

                if ($sql_result !== TRUE) {
                        return array("mysqli_error" => $conn->error);
                }

                $cats_table = $table . "_categories";
                $values = implode(',', $categories_array);
                $sql = "INSERT INTO {$cats_table} (Category) VALUES {$values}";
                $sql_result = $conn->query($sql);

                if ($sql_result !== TRUE) {
                        return array("mysqli_error" => $conn->error);
                }

                $brands_table = $table . "_brands";
                $values = implode(',', $brands_array);
                $sql = "INSERT INTO {$brands_table} (Brand) VALUES {$values}";
                $sql_result = $conn->query($sql);

                if ($sql_result !== TRUE) {
                        return array("mysqli_error" => $conn->error);
                }

//                $sql = "UPDATE {$table} INNER JOIN {$group_table} ON {$table}.Product_ID = {$group_table}.Product_ID SET {$table}.Parent = {$group_table}.Parent, {$table}.Image = {$group_table}.Image, {$table}.Selling = TRUE";
//                $sql = "UPDATE {$table} INNER JOIN {$group_table} SET {$table}.Parent = {$group_table}.Parent, {$table}.Image = {$group_table}.Image, {$table}.Selling = TRUE WHERE {$table}.Product_ID IN {$group_table}.Product_IDs";

                $sql = "UPDATE {$table} SET Selling = TRUE";

//                $sql = "SELECT  {$group_table}.Parent, {$group_table}.Image FROM {$group_table} INNER JOIN {$table} WHERE {$table}.Product_ID IN {$group_table}.Product_IDs";

                $sql_result = $conn->query($sql);

                if ($sql_result !== TRUE) {
                        return array("mysqli_error" => $conn->error);
                }
        } else {
                return array("mysqli_error" => $conn->error);
        }
}

function getGroups($conn, $table_name) {

        $results = $conn->query("SELECT Parent, Product_IDs FROM {$table_name}" . "_groups");
        if ($results === FALSE) {
                return array("mysqli_error" => $conn->error);
        } else {
                $group_array = [];
                while ($row = $results->fetch_assoc()) {
                        if (array_key_exists($row['Parent'], $group_array)) {
                                $group_array[$row['Parent']] = $group_array[$row['Parent']] . ',' . $row['Product_IDs'];
                        } else {
                                $group_array[$row['Parent']] = $row['Product_IDs'];
                        }
                }
                return $group_array;
        }
}
