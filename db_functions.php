<?php

function connectToServer() { // ***USING***
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

function connectToDb($db_name) { // ***USING***
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

function openDB($db_name) { // ***USING***
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

function getTables() { // ***USING***
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

function getProductsBySKU($SKU, $table_name) {

        $conn = openDB('rwk_productchooserdb');
        $result = $conn->query("SELECT * FROM {$table_name} WHERE Selling = TRUE AND Parent = '{$SKU}'");
        return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductsByProductCode($ProductCode, $table_name) {

        $conn = openDB('rwk_productchooserdb');
        $result = $conn->query("SELECT * FROM {$table_name} WHERE Selling = TRUE AND Parent = '{$ProductCode}'");
        return $result->fetch_all(MYSQLI_ASSOC);
}

function getProductData($conn, $table_name, $start_row, $items_per_page, $filters, $previous_page = FALSE) { // ***USING***
        $sql = "SELECT * FROM {$table_name}";

        if ($filters[0] !== FALSE AND $filters[0] !== 'All') {
                $filter_groups = [];
                foreach ($filters as $filter) {
                        $pos = strpos($filter, "=");
                        $lhs = substr($filter, 0, $pos);
                        $lhs = trim($lhs);
                        $rhs = substr($filter, $pos + 1);
                        $rhs = trim($rhs);
                        $rhs = "'$rhs'";

                        if (array_key_exists($lhs, $filter_groups)) {
                                $filter_groups[$lhs] = $filter_groups[$lhs] . ',' . $rhs;
                        } else {
                                $filter_groups[$lhs] = $rhs;
                        }
                }
                $sql .= " WHERE ";
                foreach ($filter_groups as $key => $filter) {
                        $sql .= "{$key} IN ({$filter})";
                        $sql .= " AND ";
                }
                $sql = rtrim($sql, 'AND ');
        }
        $sql .= " LIMIT {$start_row}, {$items_per_page}";

        $results = $conn->query($sql);

        if ($results !== FALSE) {
                foreach ($results as $row) {

                        $array[] = [
                            'Selling' => $row['Selling'],
                            'Product_ID' => $row['Product_ID'],
                            'Name' => $row['Name'],
                            'SKU' => $row['SKU'],
                            'Price_RRP' => $row['Price_RRP'],
                            'Trade_Price' => $row['Trade_Price'],
                            'Description' => $row['Description'],
                            'Image' => $row['Image'],
                            'Colour' => $row['Colour'],
                            'Size' => $row['Size'],
                            'Stock_Type' => $row['Stock_Type'],
                            'Stock_Level' => $row['Stock_Level'],
                            'Brand' => $row['Brand']
                        ];
                }
                return $array;
        } else {
                return array("mysqli_error" => $conn->error);
        }
}

function updateSellingDB($conn, $table_name, $selling_list) { // ***USING***
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

function bulkFillTable($conn, $file_name) { // ***USING***
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
        $sql = "ALTER TABLE {$table} ADD Selling VARCHAR(255) FIRST, ADD Parent VARCHAR(255), ADD Local_Images VARCHAR(255), ADD Local_SKU VARCHAR(255) FIRST, ADD PRIMARY KEY(`Product_ID`)";

        $result = $conn->query($sql);
        if ($result !== TRUE) {
                return array("mysqli_error" => $conn->error);
        }
}

function createGroupsTable($conn, $file_name) { // ***USING***
        $table = str_replace(".csv", "_groups", $file_name);

        $sql = "DROP TABLE IF EXISTS {$table}";
        if ($conn->query($sql) === FALSE) {
                return array("mysqli_error" => $conn->error);
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (Group_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Parent VARCHAR(255), Product_ID VARCHAR(255), Image VARCHAR(255))";

        if ($conn->query($sql) === TRUE) {
                return TRUE;
        } else {
                return FALSE;
        }
}

function createBrandsTable($conn, $file_name) { // ***USING***
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

function createCategoriesTable($conn, $file_name) { // ***USING***
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

// returns an array of the image fields for one row of data 
function getAllImageFields($conn, $table, $product_id) {

        $image_array = [];

        $fields = 'Image, Image_1, Image_2, Image_3, Image_4, Image_5, Image_6, Image_7, Image_8, Image_9, Image_10';
        $sql = "SELECT  {$fields} FROM {$table} WHERE Product_ID = {$product_id}";
        $results = $conn->query($sql);
        $results_array = mysqli_fetch_all($results, MYSQLI_ASSOC);
        if ($results === FALSE) {
                return array("mysqli_error" => $conn->error);
        } else {
                return $results_array;
        }
}

//function getImageArray($conn, $table) {
//
//    $image_array = [];
//
//    $fields = 'Image, Image_1, Image_2, Image_3, Image_4, Image_5, Image_6, Image_7, Image_8, Image_9, Image_10';
//    $sql = "SELECT  {$fields} FROM {$table}";
//    $results = $conn->query($sql);
//    $results_array = mysqli_fetch_all($results, MYSQLI_ASSOC);
//    if ($results === FALSE) {
//        return array("mysqli_error" => $conn->error);
//    } else {
////        $value = str_replace(',', ' ', $results_array['Image']);
////        $value = explode(' ', $results_array['Image']);
////        $image_array [] = $results_array['Image'][0];
//        foreach ($results_array as $values) {
//            $temp = str_replace(',', ' ', $values['Image']);
//            $url_array = explode(' ', $temp);
//            $image_array [] = $url_array[0];
//            foreach ($values as $image) {
//                $temp = str_replace(',', ' ', $image);
//                $url_array = explode(' ', $temp);
//                foreach ($url_array as $url) {
//                    if (!in_array($url, $image_array) && stripos($url, 'https://') === 0) {
//                        $image_array [] = $url;
//                    }
//                }
//            }
//        }
//    }
//    return $image_array;
//}

function reformatTable($conn, $file_name) {

        $table = str_replace(".csv", "", $file_name);
        $groups_array = [];
        $brands_array = [];
        $categories_array = [];
        $fields = 'Product_ID, SKU, Name, Product_Range, Brand, Categories, Image, Image_1, Image_2, Image_3, Image_4, Image_5, Image_6, Image_7, Image_8, Image_9, Image_10';
        $sql = "SELECT  {$fields} FROM {$table}";

        $results = $conn->query($sql);
        if ($results !== FALSE) {

                while ($row = $results->fetch_assoc()) {

//            $baseSKU = getBaseSKU($row['SKU']);
//            $baseSKU = str_replace("'", '', $baseSKU);
//            $baseSKU = "'$baseSKU'";

                        if ($row['Product_Range'] === "") {
                                $ParentSKU = $row['Name'];
                        } else {
                                $ParentSKU = $row['Product_Range'];
                        }
                        $ParentSKU = generateParentSKU($ParentSKU);
                        // get $ParentSKU from Product_Range or if blank from Name.
                        // remove ()/ and replace with _
                        // convert to all upper case
                        // remove colour from end of string

                        $ParentSKU = "'$ParentSKU'";

                        $Product_ID = $row['Product_ID'];
                        $Product_ID = "'$Product_ID'";

                        if (!empty($row['Brand']) && !in_array($row['Brand'], $brands_array)) {
                                $brands_array[] = $row['Brand'];
                        }

                        if (!empty($row['Categories'])) {
                                $array = splitCats($row['Categories']);
                                foreach ($array as $cat) {
                                        if (!in_array($cat, $categories_array)) {
                                                $categories_array[] = $cat;
                                        }
                                }
                        }

                        /**         Get images when they are needed on page * */
//            $image_array = getImageArray($row);
                        $image_array = createImageArray($row);
                        $image_string = implode(',', $image_array);
//            $image = getImage($row['Image'], $brand, $row['SKU']);
//            if (!empty($image_array)) {
//                $index = 0;
//                foreach ($image_array as $item) {
//                    $image = $image . getImage($item, $row['Brand'], $row['SKU'], $index) . ', ';
//                    $index ++;
//                }
//            }
                        $image_string = rtrim($image_string, ', ');
                        $image_string = "'$image_string'";

                        $groups_array[] = "({$ParentSKU},{$Product_ID},{$image_string})";
                }

                $group_table = $table . "_groups";
                $values = implode(',', $groups_array);
                $sql = "INSERT INTO {$group_table} (Parent, Product_ID, Image) VALUES {$values}"; // change Product_Range to Parent_SKU
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

                $sql = "UPDATE {$table} INNER JOIN {$group_table} ON {$table}.Product_ID = {$group_table}.Product_ID SET {$table}.Parent = {$group_table}.Parent, {$table}.Image = {$group_table}.Image, {$table}.Selling = TRUE";
                $sql_result = $conn->query($sql);

                if ($sql_result !== TRUE) {
                        return array("mysqli_error" => $conn->error);
                }
        } else {
                return array("mysqli_error" => $conn->error);
        }
}

function getGroups($conn, $table_name) {

        $results = $conn->query("SELECT Parent, Product_ID FROM {$table_name}" . "_groups");
        if ($results === FALSE) {
                return array("mysqli_error" => $conn->error);
        } else {
                $group_array = [];
                while ($row = $results->fetch_assoc()) {
                        if (array_key_exists($row['Parent'], $group_array)) {
                                $group_array[$row['Parent']] = $group_array[$row['Parent']] . ',' . $row['Product_ID'];
                        } else {
                                $group_array[$row['Parent']] = $row['Product_ID'];
                        }
                }
                return $group_array;
        }
}
