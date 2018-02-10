<?php

function getProductData($conn, $table_name, $group_id, $items_per_page, $filter, $previous_page = FALSE) {
        // for $start_row read group_id
        $product_count = 0;
        $table = $table_name . '_groups';
        $groups_results = $conn->query("SELECT Group_ID, Variant_IDs FROM {$table}");
        if ($groups_results !== FALSE) {
                $table = $table_name . '_variants';
                foreach ($groups_results as $group_row) {
                        if ($group_row['Group_ID'] > $group_id) {
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
                                        $_POST['current_row'] = $group_row['Group_ID'];
                                        if ($product_count >= $items_per_page) {
                                                break;
                                        }
                                }
                        }
                }
                return $array;
        } else {
                return array("mysqli_error" => $conn->error);
        }
}
