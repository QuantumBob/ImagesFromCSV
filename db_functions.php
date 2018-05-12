<?php

function connectToServer () {

        $servername = "localhost";
        $username = "root";
        $password = "";

        //create connection
        $conn = new mysqli ( $servername, $username, $password );
        //check conneciton
        if ( $conn -> connect_error ) {
                die ( "Connection failed: " . $conn -> connect_error );
        }
        return $conn;
}

function connectToDb ( $db_name ) {

        $servername = "localhost";
        $username = "root";
        $password = "";

        //create connection
        $conn = new mysqli ( $servername, $username, $password, $db_name );
        //check conneciton
        if ( $conn -> connect_error ) {
                die ( "Connection failed: " . $conn -> connect_error );
        }
        return $conn;
}

function openDB ( $db_name ) {

        // connect to server
        $conn = connectToServer ();

        $sql = "CREATE DATABASE IF NOT EXISTS  {$db_name}";
        if ( $conn -> query ( $sql ) !== TRUE ) {
                die ( "Error creating database: " . $conn -> error );
        }

        // connect to database
        $conn = connectToDb ( $db_name );

        return $conn;
}

function tableExists ( $conn, $table ) {

        $sql = "SHOW TABLES LIKE '$table'";
        $res = $conn -> query ( $sql );

        if ( $res === 0 ) {
                return 0;
        } else {
                foreach ( $res as $test ) {
                        $testty = $test;
                }
                return $res -> num_rows === 1;
        }
}

function getTables () {

        $conn = openDB ( 'rwk_productchooserdb' );
        $result = $conn -> query ( "SELECT table_name FROM information_schema.tables where table_schema='rwk_productchooserdb'" );

        $html = array ();

        $html[] = '<div id="tables">';
        foreach ( $result as $table ) {
                if ( stripos ( $table[ 'table_name' ], '_groups' ) === FALSE && stripos ( $table[ 'table_name' ], '_map' ) === FALSE && stripos ( $table[ 'table_name' ], '_brands' ) === FALSE && stripos ( $table[ 'table_name' ], '_categories' ) === FALSE && stripos ( $table[ 'table_name' ], '_extra' ) === FALSE && stripos ( $table[ 'table_name' ], '_images' ) === FALSE && stripos ( $table[ 'table_name' ], '_filtered' ) === FALSE ) {
                        $html[] = "<input id='{$table[ 'table_name' ]}' class='button_class gen_table_btn'  type='button' name= '{$table[ 'table_name' ]}' value='{$table[ 'table_name' ]}' />";
                }
        }
        $html[] = '</div>';
        echo implode ( ' ', $html );
}

function getProductsByID ( $conn, $table_name, $IDs ) {

        $result = $conn -> query ( "SELECT * FROM {$table_name} WHERE Product_ID IN ({$IDs})" ); // AND Selling = TRUE" );

        return $result -> fetch_all ( MYSQLI_ASSOC );
}

function getProductsBySelling ( $tableName ) {

        $conn = openDB ( 'rwk_productchooserdb' );
        $sql = "SELECT * FROM {$tableName}_extra WHERE Selling = TRUE";
        $results = $conn -> query ( $sql );
        $error = $conn -> error;
        $conn -> close ();

        if ( $results !== FALSE ) {
                return $results;
        } else {
                return FALSE;
        }
}

function getBrands ( $table_name ) {

        $conn = openDB ( 'rwk_productchooserdb' );
        $sql = "SELECT * FROM {$table_name}_brands";
        $results = $conn -> query ( $sql );
        $error = $conn -> error;
        $conn -> close ();

        if ( $results !== FALSE ) {
                return $results;
        } else {
                return FALSE;
        }
}

function getCategories ( $table_name ) {

        $conn = openDB ( 'rwk_productchooserdb' );
        $sql = "SELECT * FROM {$table_name}_categories";
        $results = $conn -> query ( $sql );
        $error = $conn -> error;
        $conn -> close ();

        if ( $results !== FALSE ) {
                return $results;
        } else {
                return FALSE;
        }
}

function createFilteredTable ( $filters ) {

        $table = $GLOBALS[ 'tablename' ];

        $conn = openDB ( 'rwk_productchooserdb' );

        $sql = "DROP TABLE IF EXISTS {$table}_filtered";
        if ( $conn -> query ( $sql ) === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        }

        $where = FALSE;

        $sql = "CREATE TABLE  {$table}_filtered SELECT * FROM {$table}";

        if ( $filters[ 0 ] !== FALSE AND $filters[ 0 ] !== 'All' ) {

                $filter_groups = [];
                foreach ( $filters as $filter ) {
                        $pos = strpos ( $filter, "=" );
                        $lhs = substr ( $filter, 0, $pos );
                        $lhs = trim ( $lhs );
                        $rhs = substr ( $filter, $pos + 1 );
                        $rhs = trim ( $rhs );
//                                        $rhs = "'$rhs'";

                        if ( array_key_exists ( $lhs, $filter_groups ) ) {
                                $filter_groups[ $lhs ] = $filter_groups[ $lhs ] . ',' . $rhs;
                        } else {
                                $filter_groups[ $lhs ] = $rhs;
                        }
                }
                $sql .= " WHERE ";
                foreach ( $filter_groups as $key => $filter ) {
                        if ( $key === 'Brand' && $filter === 'All' ) {
                                continue;
                        }
                        if ( $key === 'Categories' ) {
                                $sql .= "{$key} LIKE  '%{$filter}%'";
                                continue;
                        }
                        $sql .= "{$key} IN ('{$filter}')";
                        $sql .= " AND ";
                }
                $sql = rtrim ( $sql, 'AND ' );
        }

        $product_results = $conn -> query ( $sql );
}

function getGroupedProductData ( $conn, $table_name, $start_row, $items_per_page, $previous_page = FALSE ) {

        $sql = "SELECT DISTINCT Parent FROM {$table_name}_extra";
//        $sql = "SELECT * FROM {$table_name}_extra";// ORDER BY Parent ASC";
        $sql .= " LIMIT {$start_row}, 1000000";
        $groups_results = $conn -> query ( $sql );
        $array = [];

        if ( $groups_results !== FALSE ) {
                $start_group = 0;
                $num_groups = 0;
                foreach ( $groups_results as $group_row ) {

                        if ( $num_groups >= $items_per_page ) {
                                return $array;
                        }
                        $image_group = "";

                        $parent = $group_row[ 'Parent' ];
                        $parent = "'$parent'";

                        $sql = "SELECT * FROM {$table_name}_filtered INNER JOIN {$table_name}_extra ON  {$table_name}_filtered.Product_ID =  {$table_name}_extra.Product_ID  WHERE  {$table_name}_extra.Parent = {$parent}";

                        $product_results = $conn -> query ( $sql );

//                        $sql = "SELECT * FROM {$table_name}_extra WHERE Product_ID IN ({$group_row[ 'Product_IDs' ]})";
//                        $selling_results = $conn -> query ( $sql );
//                        $selling_array = [];
//                        foreach ( $selling_results as $selling_id ) {
//                                $selling_array[ $selling_id[ 'Product_ID' ] ] = $selling_id[ 'Selling' ];
//                        }
                        if ( $product_results !== FALSE && $product_results -> num_rows !== 0 ) {
                                $num_groups ++;
                                foreach ( $product_results as $product ) {

//                                        $image_array = createImageArray ( $product );
//                                        $image_array = downloadImage ( $product, $image_array );

                                        $image_array_in = explode ( ',', $product[ 'Image' ] );
                                        $image_array_out = [];
                                        $update = FALSE;
                                        $index = 0;

                                        foreach ( $image_array_in as $url ) {
                                                if ( substr ( $url, 0, 8 ) !== './media/' ) {
                                                        $new_url = getImage ( $url, $product[ 'Brand' ], $product[ 'SKU' ], $index );

                                                        // need to update url in database
                                                        if ( $new_url !== $url ) {
                                                                $url = $new_url;
                                                                $update = TRUE;
                                                        }
                                                }
                                                $index ++;
                                                $image_array_out[] = $url;
                                        }

                                        $images = implode ( ',', $image_array_out );
                                        $image_group .= ',' . $images;

                                        if ( $update ) {
                                                $result = updateImageField ( $conn, $table_name, $images, $product[ 'Product_ID' ] );
                                        }

//                                        if ( array_key_exists ( $product[ 'Product_ID' ], $selling_array ) ) {
//                                                $selling = $selling_array[ $product[ 'Product_ID' ] ];
//                                        } else {
//                                                $selling = FALSE;
//                                        }

                                        $grouped_array[] = [
//                                            'Selling' => $selling,
                                            'Selling' => $product[ 'Selling' ],
                                            'Product_ID' => $product[ 'Product_ID' ],
                                            'Name' => $product[ 'Name' ],
                                            'SKU' => $product[ 'SKU' ],
                                            'Price_RRP' => $product[ 'Price_RRP' ],
                                            'Trade_Price' => $product[ 'Trade_Price' ],
                                            'Description' => $product[ 'Description' ],
                                            'Image' => $images,
                                            'Colour' => $product[ 'Colour' ],
                                            'Size' => $product[ 'Size' ],
                                            'Stock_Type' => $product[ 'Stock_Type' ],
                                            'Stock_Level' => $product[ 'Stock_Level' ],
                                            'Brand' => $product[ 'Brand' ]
                                        ];
                                }
                                $image_group = ltrim ( $image_group, ',' );
//                                $array[] = array ( 'group_id' => $group_row[ 'Group_ID' ], 'images' => $image_group, 'products' => $grouped_array );
                                $array[] = array ( 'images' => $image_group, 'products' => $grouped_array );
                                unset ( $grouped_array );
                        }
                }
                return $array;
        } else {
                return array ( "mysqli_error" => $conn -> error );
        }
}

function oldgetGroupedProductData ( $conn, $table_name, $start_row, $items_per_page, $filters, $previous_page = FALSE ) {

        $sql = "SELECT Group_ID, Product_IDs FROM {$table_name}_groups";
        $sql .= " LIMIT {$start_row}, 1000000";
        $group_results = $conn -> query ( $sql );
        $array = [];

        if ( $group_results !== FALSE ) {
                $start_group = 0;
                $num_groups = 0;
                foreach ( $group_results as $group_row ) {

                        if ( $num_groups >= $items_per_page ) {
                                return $array;
                        }
                        $image_group = "";

                        $sql = "SELECT * FROM {$table_name} WHERE Product_ID IN ({$group_row[ 'Product_IDs' ]})";

                        if ( $filters[ 0 ] !== FALSE AND $filters[ 0 ] !== 'All' ) {
                                $filter_groups = [];
                                foreach ( $filters as $filter ) {
                                        $pos = strpos ( $filter, "=" );
                                        $lhs = substr ( $filter, 0, $pos );
                                        $lhs = trim ( $lhs );
                                        $rhs = substr ( $filter, $pos + 1 );
                                        $rhs = trim ( $rhs );
//                                        $rhs = "'$rhs'";

                                        if ( array_key_exists ( $lhs, $filter_groups ) ) {
                                                $filter_groups[ $lhs ] = $filter_groups[ $lhs ] . ',' . $rhs;
                                        } else {
                                                $filter_groups[ $lhs ] = $rhs;
                                        }
                                }
                                $sql .= " AND ";
                                foreach ( $filter_groups as $key => $filter ) {
                                        if ( $key === 'Brand' && $filter === 'All' ) {
                                                continue;
                                        }
                                        if ( $key === 'Categories' ) {
                                                $sql .= "{$key} LIKE  '%{$filter}%'";
                                                continue;
                                        }
                                        $sql .= "{$key} IN ('{$filter}')";
                                        $sql .= " AND ";
                                }
                                $sql = rtrim ( $sql, 'AND ' );
                        }

                        $product_results = $conn -> query ( $sql );

                        $sql = "SELECT * FROM {$table_name}_extra WHERE Product_ID IN ({$group_row[ 'Product_IDs' ]})";
                        $selling_results = $conn -> query ( $sql );
                        $selling_array = [];
                        foreach ( $selling_results as $selling_id ) {
                                $selling_array[ $selling_id[ 'Product_ID' ] ] = $selling_id[ 'Selling' ];
                        }


                        if ( $product_results !== FALSE && $product_results -> num_rows !== 0 ) {
                                $num_groups ++;
                                foreach ( $product_results as $product ) {
                                        $image_array_in = explode ( ',', $product[ 'Image' ] );
                                        $image_array_out = [];
                                        $update = FALSE;
                                        $index = 0;

                                        foreach ( $image_array_in as $url ) {
                                                if ( substr ( $url, 0, 8 ) !== './media/' ) {
                                                        $url = getImage ( $url, $product[ 'Brand' ], $product[ 'SKU' ], $index );
                                                        // need to update url in database
                                                        $update = TRUE;
                                                }
                                                $index ++;
                                                $image_array_out[] = $url;
                                        }

                                        $images = implode ( ',', $image_array_out );
                                        $image_group .= ',' . $images;

                                        if ( $update ) {
                                                $result = updateImageField ( $conn, $table_name, $images, $product[ 'Product_ID' ] );
                                        }

                                        if ( array_key_exists ( $product[ 'Product_ID' ], $selling_array ) ) {
                                                $selling = $selling_array[ $product[ 'Product_ID' ] ];
                                        } else {
                                                $selling = FALSE;
                                        }

                                        $grouped_array[] = [
                                            'Selling' => $selling,
                                            'Product_ID' => $product[ 'Product_ID' ],
                                            'Name' => $product[ 'Name' ],
                                            'SKU' => $product[ 'SKU' ],
                                            'Price_RRP' => $product[ 'Price_RRP' ],
                                            'Trade_Price' => $product[ 'Trade_Price' ],
                                            'Description' => $product[ 'Description' ],
                                            'Image' => $images,
                                            'Colour' => $product[ 'Colour' ],
                                            'Size' => $product[ 'Size' ],
                                            'Stock_Type' => $product[ 'Stock_Type' ],
                                            'Stock_Level' => $product[ 'Stock_Level' ],
                                            'Brand' => $product[ 'Brand' ]
                                        ];
                                }
                                $image_group = ltrim ( $image_group, ',' );
                                $array[] = array ( 'group_id' => $group_row[ 'Group_ID' ], 'images' => $image_group, 'products' => $grouped_array );
                                unset ( $grouped_array );
                        }
                }
                return $array;
        } else {
                return array ( "mysqli_error" => $conn -> error );
        }
}

function updateImageField ( $conn, $table_name, $imageString, $id ) {

        $update = "UPDATE {$table_name} SET Image = '{$imageString}' WHERE Product_ID = {$id}";

        $sql = $update;
        if ( $conn -> query ( $sql ) ) {
                return TRUE;
        } else {
                return array ( "mysqli_error" => $conn -> error );
        }
}

function updateSelling () {

        if ( ! isset ( $_SESSION ) ) {
                session_start ();
        }
        $table_name = $GLOBALS[ 'tablename' ];
        $conn = openDB ( 'rwk_productchooserdb' );

        $checkbox = json_decode ( $_POST[ 'selling' ], TRUE );
        $selling = $checkbox[ 'checked' ] ? 'TRUE' : 'FALSE';
        $sql = "UPDATE {$table_name}_extra SET Selling = {$selling} WHERE Product_ID = {$checkbox[ 'id' ]}";

        if ( $conn -> query ( $sql ) ) {
                $conn -> close ();
                return TRUE;
        } else {
                $error = $conn -> error;
                $conn -> close ();
                return array ( "mysqli_error" => $error );
        }
}

function getLargestID ( $table_name ) {

        $conn = openDB ( 'rwk_productchooserdb' );
        $result = $conn -> query ( "SELECT MAX(Product_ID) AS max_id FROM $table_name" );
        $row = $result -> fetch_assoc ();
        return $row[ 'max_id' ];
}

function loadCSVData ( $conn, $filename ) {

        $table = $GLOBALS[ 'tablename' ];

        //if main table doesn't exists create it
//        if ( ! tableExists ( $conn, $table ) ) {
//                $fields_array = getCSVHeaders ( $filename );
//
//                if ( ! isset ( $_SESSION ) ) {
//                        session_start ();
//                }
//                $_SESSION[ 'table_name' ] = $table;
//                $last_field = createMainTable ( $conn, $table, $fields_array );
//        }

        $fullFilepath = $GLOBALS[ 'filepath' ] . $filename;

        $sql = "LOAD DATA LOCAL INFILE '{$fullFilepath}' REPLACE INTO TABLE {$table} FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' IGNORE 1 LINES";

        $result = $conn -> query ( $sql );
        if ( $result !== TRUE ) {
                return array ( "mysqli_error" => $conn -> error );
        }

        // create supporting tables
//        if ( ! tableExists ( $conn, $table . '_groups' ) ) {
//                createGroupsTable ( $conn, $table );
//        }
//        if ( ! tableExists ( $conn, $table . '_categories' ) ) {
//                createCategoriesTable ( $conn, $table );
//        }
//        if ( ! tableExists ( $conn, $table . '_brands' ) ) {
//                createBrandsTable ( $conn, $table );
//        }
//        if ( ! tableExists ( $conn, $table . '_extra' ) ) {
//                createSellingTable ( $conn, $table );
//        }
        // reformat main table to include extra fields
//        reformatMainTable ( $conn, $table );
}

function bulkFillTable ( $conn, $file_name ) {

//        $table = str_replace ( ".csv", "", $file_name );
        $table = $GLOBALS[ 'tablename' ];

        //if table exists return
        if ( tableExists ( $conn, $table ) ) {
                return 0;
        }

        $fields_array = getCSVHeaders ( $file_name );

        if ( ! isset ( $_SESSION ) ) {
                session_start ();
        }
        $_SESSION[ 'table_name' ] = $table;

        $last_field = createMainTable ( $conn, $table, $fields_array );

        $file_name = "C:/wamp64/www/StockPicker/resources/" . $file_name;
        // get number of rows in file
        $file = new SplFileObject ( $file_name, 'r' );
        $file -> seek ( PHP_INT_MAX );
        $num_rows_in_file = $file -> key () + 1;

        $sql = "SELECT COUNT(*) FROM {$table}";
        if ( $last_field != NULL ) {
                $sql .= " GROUP BY {$last_field}";
        }
        $result = $conn -> query ( $sql );
        if ( $result === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        } else {
                $num_rows_in_table = mysqli_num_rows ( $result );
        }

        if ( $num_rows_in_table !== 0 ) {
                $sql = "TRUNCATE {$table}";
                if ( $conn -> query ( $sql ) === FALSE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }
        }

//    $sql = "LOAD DATA INFILE '{$file_name}' INTO TABLE {$table} CHARACTER SET UTF8 FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
        $sql = "LOAD DATA LOCAL INFILE '{$file_name}' INTO TABLE {$table} FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES";

        $result = $conn -> query ( $sql );
        if ( $result !== TRUE ) {
                return array ( "mysqli_error" => $conn -> error );
        }
        // "ALTER TABLE `alterego_current_stockline_green` ADD PRIMARY KEY(`Product_ID`);"
        $sql = "ALTER TABLE {$table} ADD Selling BOOLEAN FIRST, ADD Parent VARCHAR(255), ADD Local_Images VARCHAR(255), ADD Local_SKU VARCHAR(255) FIRST, ADD PRIMARY KEY(`Product_ID`)";

        $result = $conn -> query ( $sql );
        if ( $result !== TRUE ) {
                return array ( "mysqli_error" => $conn -> error );
        }

        // create supporting tables
        createGroupsTable ( $conn, $table );
        createCategoriesTable ( $conn, $table );
        createBrandsTable ( $conn, $table );
        // reformat main table to include extra fields
        updateSupportTables ( $conn, $table );
}

function createMainTable ( $conn, $filename ) {//, $fields_array ) {
//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }
        $table = $GLOBALS[ 'tablename' ];

        if ( ! tableExists ( $conn, $table ) ) {

                $fields_array = getCSVHeaders ( $filename );

                if ( ! isset ( $_SESSION ) ) {
                        session_start ();
                }
                $_SESSION[ 'table_name' ] = $table;

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (";
                $type = 'VARCHAR(256)';

                foreach ( $fields_array as $field ) {

                        $field = str_replace ( ' ', '_', $field );
                        $field = str_replace ( '(', '', $field );
                        $field = str_replace ( ')', '', $field );
                        $field = str_replace ( ',', '', $field );

                        if ( stripos ( $field, 'description' ) !== FALSE ) {
                                $sql .= $field . "  TEXT,";
                        } elseif ( stripos ( $field, 'image' ) !== FALSE ) {
                                $sql .= $field . "  VARCHAR(512),";
                        } else {
                                $sql .= $field . "  " . $type . ",";
                        }
                }
                $sql .= " PRIMARY KEY (Product_ID))";

                if ( $conn -> query ( $sql ) !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                } else {
                        return $field;
                }
        }
}

function createGroupsTable ( $conn ) {

        $table = $GLOBALS[ 'tablename' ] . '_groups';

//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }

        if ( ! tableExists ( $conn, $table ) ) {

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (Group_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Parent VARCHAR(255) UNIQUE, Product_IDs VARCHAR(2048), Image VARCHAR(2048))";

                if ( $conn -> query ( $sql ) === TRUE ) {
                        return TRUE;
                } else {
                        return FALSE;
                }
        }
}

function createBrandsTable ( $conn ) {

        $table = $GLOBALS[ 'tablename' ] . '_brands';

//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }

        if ( ! tableExists ( $conn, $table ) ) {

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (Brand_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Brand VARCHAR(255) UNIQUE)";

                if ( $conn -> query ( $sql ) === TRUE ) {
                        return TRUE;
                } else {
                        return FALSE;
                }
        }
}

function createImagesTable ( $conn ) {

        $table = $GLOBALS[ 'tablename' ] . '_images';

//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }

        if ( ! tableExists ( $conn, $table ) ) {

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (Product_ID INT(10), Remote_Url VARCHAR(512), Local_Url TEXT,";
                $sql .= " PRIMARY KEY (Product_ID, Remote_Url))";

                if ( $conn -> query ( $sql ) === TRUE ) {
                        return TRUE;
                } else {
                        return FALSE;
                }
        }
}

function createExtraTable ( $conn ) {

        $table = $GLOBALS[ 'tablename' ] . '_extra';

//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }

        if ( ! tableExists ( $conn, $table ) ) {

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (Product_ID INT(10), Selling BOOLEAN DEFAULT FALSE,";

                $wooFields = getResourceFromXML ( $GLOBALS[ 'res_file' ], 'alterego_map', "map", TRUE );

                $type = 'VARCHAR(512)';

                foreach ( $wooFields as $field ) {
                        $field = str_replace ( ' ', '_', $field );
                        $field = str_replace ( '?', '', $field );
                        $field = str_replace ( '(', '', $field );
                        $field = str_replace ( ')', '', $field );
                        $field = str_replace ( ',', '', $field );
                        $field = str_replace ( '-', '_', $field );
                        $sql .= $field . "  " . $type . ",";
                }
                $sql .= " PRIMARY KEY (Product_ID))";

                if ( $conn -> query ( $sql ) === TRUE ) {
                        return TRUE;
                } else {
                        return FALSE;
                }
        }
}

function createCategoriesTable ( $conn ) {

        $table = $GLOBALS[ 'tablename' ] . '_categories';

        if ( ! tableExists ( $conn, $table ) ) {

//        $sql = "DROP TABLE IF EXISTS {$table}";
//        if ( $conn -> query ( $sql ) === FALSE ) {
//                return array ( "mysqli_error" => $conn -> error );
//        }

                $sql = "CREATE TABLE IF NOT EXISTS {$table} (Category_ID INT(10) PRIMARY KEY AUTO_INCREMENT, Category VARCHAR(255) UNIQUE)";

                if ( $conn -> query ( $sql ) === TRUE ) {
                        return TRUE;
                } else {
                        return FALSE;
                }
        }
}

function updateSupportTables ( $conn ) {

        $groups_array = [];
        $brands_array = [];
        $selling_parent_array = [];
        $image_array = [];
        $categories_array = [];
        $image_table_array = [];

        $table = $GLOBALS[ 'tablename' ];

        $fields = 'Product_ID, SKU, Name, Product_Range, Brand, Categories, Image, Image_1, Image_2, Image_3, Image_4, Image_5, Image_6, Image_7, Image_8, Image_9, Image_10';

        $sql = "SELECT  {$fields} FROM {$table}";

        $results = $conn -> query ( $sql );

        if ( $results !== FALSE ) {

                while ( $row = $results -> fetch_assoc () ) {

                        $ParentSKU = $row[ 'Product_Range' ] === "" ? generateParentSKU ( $row[ 'Name' ] ) : generateParentSKU ( $row[ 'Product_Range' ] );
                        $Product_ID = $row[ 'Product_ID' ];

                        $value = "('$Product_ID','$ParentSKU')";
                        if ( ! in_array ( $value, $selling_parent_array ) ) {
                                $selling_parent_array[] = $value;
                        }

                        if ( ! empty ( $row[ 'Brand' ] ) ) {
                                $brand = $row[ 'Brand' ];
                                $brand = "'$brand'";

                                if ( ! in_array ( "({$brand})", $brands_array ) ) {
                                        $brands_array[] = "({$brand})";
                                }
                        }

                        if ( ! empty ( $row[ 'Categories' ] ) ) {
                                $array = splitCats ( $row[ 'Categories' ] );
                                foreach ( $array as $cat ) {
                                        $cat = "'$cat'";
                                        if ( ! in_array ( "({$cat})", $categories_array ) && ! in_array ( "({$cat})", $brands_array ) ) {
                                                $categories_array[] = "({$cat})";
                                        }
                                }
                        }

                        $image_array = createImageArray ( $row );
                        $image_string = implode ( ',', $image_array );
                        $image_string = rtrim ( $image_string, ', ' );
                        if ( key_exists ( $ParentSKU, $groups_array ) ) {
                                $groups_array[ $ParentSKU ][ 'Product_IDs' ] .= ',' . $Product_ID;
                        } else {
                                $groups_array[ $ParentSKU ] = array ( 'Product_IDs' => $Product_ID, 'Image_string' => $image_string );
                        }

//                        $value = "('$ParentSKU','$Product_IDs','$image_string')";
//                        if ( ! in_array ( $value, $groups_array ) ) {
//                                $groups_array[] = $value;
//                        }                      

                        $index = 0;
                        foreach ( $image_array as $remote_url ) {



                                $media_dir = $GLOBALS[ 'media_dir' ];

                                if ( ! is_dir ( $media_dir ) ) {
                                        mkdir ( $media_dir );
                                }


                                $media_dir = $media_dir . strtolower ( $row[ 'Brand' ] ) . '/';
                                $media_dir = str_replace ( ' ', '', $media_dir );
                                $SKU = str_replace ( '/', '', $row[ 'SKU' ] );
                                $SKU = str_replace ( '\'', '', $row[ 'SKU' ] );

                                if ( ! is_dir ( $media_dir ) ) {
                                        mkdir ( $media_dir );
                                }

                                $path_parts = pathinfo ( $remote_url );
                                if ( isset ( $path_parts[ 'extension' ] ) && ! empty ( $path_parts[ 'extension' ] ) ) {
                                        $ext = '.' . $path_parts[ 'extension' ];
                                } else {
                                        $ext = '.jpg';
                                }

                                if ( basename ( $remote_url ) === "" || basename ( $remote_url ) === "no_selection" ) {
                                        $local_url = "$media_dir . image_coming_soon.jpg";
                                } else if ( $index === 0 ) {
                                        $local_url = $media_dir . $SKU . $ext;
                                } else {
                                        $local_url = $media_dir . $SKU . '_' . $index . $ext;
                                }
                                $index ++;

                                $value = "('$Product_ID','$remote_url','$local_url')";
                                if ( ! in_array ( $value, $image_table_array ) ) {
                                        $image_table_array[] = $value;
                                }
                        }
                }

                // update images table
                $values = "";
                $values = implode ( ',', $image_table_array );

                $images_table = $table . "_images";
                $sql = "INSERT IGNORE INTO {$images_table} (Product_ID, Remote_Url, Local_Url) VALUES {$values}";
                $sql_result = $conn -> query ( $sql );

                if ( $sql_result !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }

                // update selling table inserting new rows
                $values = "";
                $values = implode ( ',', $selling_parent_array );

                $selling_table = $table . "_extra";
                $sql = "INSERT IGNORE INTO {$selling_table} (Product_ID, Parent) VALUES {$values}";
                $sql_result = $conn -> query ( $sql );

                if ( $sql_result !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }

                // update brands table inserting new rows
                $values = "";
                $values = implode ( ',', $brands_array );

                $brands_table = $table . "_brands";
                $sql = "INSERT IGNORE INTO {$brands_table} (Brand) VALUES {$values}";
                $sql_result = $conn -> query ( $sql );

                if ( $sql_result !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }

                // update categories table inserting new rows
                $values = "";
                $values = implode ( ',', $categories_array );

                $cats_table = $table . "_categories";
                $sql = "INSERT IGNORE INTO {$cats_table} (Category) VALUES {$values}";
                $sql_result = $conn -> query ( $sql );

                if ( $sql_result !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }

                // update groups table inserting new rows
                $values = "";
                foreach ( $groups_array as $key => $group_array ) {
                        $ParentSKU = "'$key'";
                        $Product_IDs = $group_array[ 'Product_IDs' ];
                        $Product_IDs = "'$Product_IDs'";
                        $image_string = $group_array[ 'Image_string' ];
                        $image_string = "'$image_string'";

                        $values = $values . ',' . "({$ParentSKU},{$Product_IDs},{$image_string})";
                }

                $values = ltrim ( $values, ',' );
                $group_table = $table . "_groups";
//                $values = implode(',', $groups_array);
                $sql = "INSERT IGNORE INTO {$group_table} (Parent, Product_IDs, Image) VALUES {$values}";
                $sql_result = $conn -> query ( $sql );

                if ( $sql_result !== TRUE ) {
                        return array ( "mysqli_error" => $conn -> error );
                }
        }
}

function _getGroups ( $conn, $table_name ) {

        $results = $conn -> query ( "SELECT Parent, Product_IDs FROM {$table_name}" . "_groups" );
        if ( $results === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        } else {
                $group_array = [];
                while ( $row = $results -> fetch_assoc () ) {
                        if ( array_key_exists ( $row[ 'Parent' ], $group_array ) ) {
                                $group_array[ $row[ 'Parent' ] ] = $group_array[ $row[ 'Parent' ] ] . ',' . $row[ 'Product_IDs' ];
                        } else {
                                $group_array[ $row[ 'Parent' ] ] = $row[ 'Product_IDs' ];
                        }
                }
                return $group_array;
        }
}

function getGroups ( $conn, $tablename, $selling = FALSE ) {

        $sql = "SELECT DISTINCT Parent FROM {$tablename}" . "_extra";
        if ( $selling ) {
                $sql .= " WHERE Selling = TRUE";
        }

        $results = $conn -> query ( $sql );
        if ( $results === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        } else {
                return $results;
        }
}

function getProductsByParent ( $conn, $tableName, $parent, $selling = FALSE ) {

        $parent = "'$parent'";

        $sql = "SELECT Product_ID FROM {$tableName}_extra WHERE Parent = $parent";
        if ( $selling ) {
                $sql .= " AND Selling = TRUE";
        }

        $results = $conn -> query ( $sql );
        if ( $results === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        }

        foreach ( $results as $id ) {
                $product_ids[] = $id[ 'Product_ID' ];
        }

        $product_ids = implode ( ',', $product_ids );

        $sql = "SELECT * FROM {$tableName} WHERE Product_ID IN ({$product_ids})";
        $results = $conn -> query ( $sql );
        if ( $results === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        } else {
                return $results -> fetch_all ( MYSQLI_ASSOC );
        }
}

function getGroup ( $conn, $table_name, $IDs ) {

        $sql = "SELECT Parent, Product_IDs FROM {$table_name}" . "_groups";
        $results = $conn -> query ( $sql );
        if ( $results === FALSE ) {
                return array ( "mysqli_error" => $conn -> error );
        } else {
                $group_array = [];
                while ( $row = $results -> fetch_assoc () ) {
                        if ( array_key_exists ( $row[ 'Parent' ], $group_array ) ) {
                                $group_array[ $row[ 'Parent' ] ] = $group_array[ $row[ 'Parent' ] ] . ',' . $row[ 'Product_IDs' ];
                        } else {
                                $group_array[ $row[ 'Parent' ] ] = $row[ 'Product_IDs' ];
                        }
                }
                return $group_array;
        }
}
