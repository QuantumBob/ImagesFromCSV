<?php

function debug_to_console($data) {
        $output = $data;
        if (is_array($output))
                $output = implode(',', $output);

        echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
}

function getFileList() { // ***USING***
        $target_dir = $GLOBALS['res_dir'];
        if ($handle = opendir($target_dir)) {
                while (FALSE !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                                if (stripos($entry, '.csv') !== FALSE) {
                                        $array[] = $entry;
                                }
                        }
                }
                closedir($handle);
                foreach ($array as $file) {
                        $html[] = "<input id='$file' class='button_class gen_btn'  type='button' name= '$file' value='$file' />";
                }
                echo implode(' ', $html);
        }
}

/*
  function doesFileExist($file_name, $upload) {

  if ($upload) {
  $target_path = $GLOBALS['res_dir'] . $file_name;

  if (file_exists($target_path)) {
  return TRUE;
  } else {
  return FALSE;
  }
  } else {
  if (($handle = fopen("$file_name", "x")) !== FALSE) {
  fclose($handle);
  return TRUE;
  } else {
  return FALSE;
  }
  }
  }
 */
function useCSV() { // ***USING***
        $target_path = $GLOBALS['res_dir'];
        if (isset($_FILES['uploadedfile']) && !empty($_FILES['uploadedfile'])) {
                $file_name = basename($_FILES['uploadedfile']['name']);
        } else if (isset($_POST['filename']) && !empty($_POST['filename'])) {
                $file_name = $_POST['filename'];
        } else {
                die();
        }

        $target_path = $target_path . $file_name;

        $headers = getCSVHeaders($file_name);
        $str = headersToHtml($headers);
        session_start();
        $_SESSION["filename"] = $file_name;

        return $str;
}

function useCSV_OLD() { // ***USING***
        $target_path = $GLOBALS['res_dir'];
        if (isset($_FILES['uploadedfile']) && !empty($_FILES['uploadedfile'])) {
                $file_name = basename($_FILES['uploadedfile']['name']);
        } else if (isset($_POST['filename']) && !empty($_POST['filename'])) {
                $file_name = $_POST['filename'];
        } else {
                die();
        }

        $target_path = $target_path . $file_name;

        $headers = getCSVHeaders($file_name);
        $str = headersToHtml($headers);
        session_start();
        $_SESSION["filename"] = $file_name;

        return $str;
}

function uploadCSV() {// ***USING ***
//    $file_name = 'alterego_current_stockline_green.csv';
//    $target_path = $GLOBALS['res_dir'];
        $file_name = basename($_FILES['uploadedfile']['name']);

//    $source_file = "D:/Documents/work/Seduce/alterego/stock_files/" . $file_name;
        $source_file = realpath($_FILES['uploadedfile']['tmp_name']);

        $file_name = str_replace('-', '_', $file_name);

        $target_path = $_SERVER['DOCUMENT_ROOT'] . '/ImagesFromCSV/resources/' . $file_name;

        if (copy($source_file, $target_path) !== TRUE) {
                return FALSE;
        }

        session_start();
        $_SESSION["filename"] = $file_name;

        return $file_name;
}

function getCSVHeaders($file_name) { // ***USING***
        $resources_dir = $GLOBALS['res_dir'];
        $file_url = $resources_dir . $file_name;

        if (($handle = fopen("$file_url", "r")) !== FALSE) {

                $headers = fgetcsv($handle, 1000, ",");
                fclose($handle);
        }
        return $headers;
}

function headersToHtml($headers) { // ***USING***
        $html_array[] = '<div class="checkbox_container">';
        foreach ($headers as $header) {
                $html_array[] = '<div class="input_class"><input type="checkbox" class="input_label" id="' . $header . '" name="' . (string) $header . '" value="' . (string) $header . '"><label for="' . $header . '">' . (string) $header . '</div>';
        }
        $html_array[] = '</div>';
        return implode(" ", $html_array);
}

function get_group_id_base($largest_id) {

        $len = strlen($largest_id);
        $base = pow(10, $len);

        return $base;
}

function getImage($file_url, $brand, $SKU, $index) { // ***USING***
        $media_dir = $GLOBALS['media_dir'];

        if (!is_dir($media_dir)) {
                mkdir($media_dir);
        }

        if (basename($file_url) === "" || basename($file_url) === "no_selection") {
                return "$media_dir . image_coming_soon.jpg";
        }

        $media_dir = $media_dir . strtolower($brand) . '/';
        $media_dir = str_replace(' ', '', $media_dir);
        $SKU = str_replace('/', '', $SKU);

        if (!is_dir($media_dir)) {
                mkdir($media_dir);
        }

        // $file_url should be one url but sometimes we are sent two
        $source = explode(',', $file_url);
        $file = $source[0];

        $image_path = $media_dir . basename($file);
        $path_parts = pathinfo($image_path);
        $ext = '.' . $path_parts['extension'];

        if ($index === 0) {
                $new_name = $media_dir . $SKU . $ext;
        } else {
                if (empty($index)) {
                        $index = 1;
                }
                $new_name = $media_dir . $SKU . '_' . $index . $ext;
        }

        // if sku name exists do nothing   
        if (file_exists($new_name)) {
                if (file_exists($image_path)) {
                        unlink($image_path);
                }
                return $new_name;
        }
        // if original image name exists change its name
        if (file_exists($image_path)) {

                $SKU = str_replace('_', '', $SKU);
                $SKU = str_replace(' ', '', $SKU);

                if (rename($image_path, $new_name)) {
                        return $new_name;
                } else {
                        $err = error_get_last();
                        return FALSE;
                }
        }
        // if no name exists download image from web and change name to sku name
        if (!file_exists($image_path)) {

                $SKU = str_replace('_', '', $SKU);
                $SKU = str_replace(' ', '', $SKU);

                $image_path = $media_dir . $SKU . $ext;
                copy($file, $image_path);
                return $image_path;
        }


        return "$media_dir . image_coming_soon.jpg";
}

// takes an array of https urls and combines them into one comma seperated string for woocommerce
//function combineImageArrayIntoString($image_array){
//    
//    foreach($image_array as $field){
//        $image_string = $image_string . $field;
//    }
//    $search = array(' ', ',');
//    $image_string = str_replace($search, '',  $image_string);
//    $image_string = str_replace('https', ',https', $image_string);
//    $image_string = ltrim($image_string, ',');
//    
//    $temp = explode(',', $image_string);
//    
//    $return_array = [];
//    foreach($temp as $url){
//        if(!in_array($url, $return_array)){
//            $return_array[] = $url;
//        }
//    }
//    $image_string = implode(',', $return_array);
//    return $image_string;
//}

function createImageArray($row) {

        if (isset($row) && !empty($row)) {

                $image_array = [];
                $fields_array = [];

                $value = str_replace(',', ' ', $row['Image']);
                $value = explode(' ', $value);
                $image_array [] = $value[0];

                foreach ($row as $key => $field) {
                        if (substr($key, 0, 5) == 'Image') {
                                $temp = str_replace(',', ' ', $field);
                                $value = explode(' ', $temp);
                                foreach ($value as $url) {
                                        if (!in_array($url, $image_array) && stripos($url, 'https://') === 0) {
                                                $image_array [] = $url;
                                        }
                                }
                        }
                }
                return $image_array;
        }
}

function getBaseName($name) {

        $resource_file = $GLOBALS['res_file'];
        $colours = getResourceFromXML($resource_file, 'alterego_colours');

        foreach ($colours as $colour) {
                $index = stripos($name, $colour);
                if ($index !== FALSE) {
                        $base_name = substr($name, 0, $index);
                        $base_name = trim($base_name, '_');
                        return $base_name;
                }
        }
        return $name;
}

/*
  function splitSKU($sku) {

  $new_sku = str_ireplace('/', '', $sku);

  $resource_file = $GLOBALS['res_file'];
  $colours = getResourceFromXML($resource_file, 'alterego_colours');

  foreach ($colours as $colour) {
  $index = stripos($new_sku, $colour);
  if ($index !== FALSE) {
  $split_sku[] = substr($new_sku, 0, $index);
  $split_sku[] = $colour;
  $split_sku[] = substr($new_sku, $index + strlen($colour));
  return $split_sku;
  }
  }

  $sizes = getResourceFromXML($resource_file, 'alterego_sizes');

  foreach ($sizes as $size) {
  $index = stripos($new_sku, $size);
  if ($index !== FALSE) {
  $split_sku[] = substr($new_sku, 0, $index);
  $split_sku[] = 'no colour';
  $split_sku[] = $size;
  return $split_sku;
  }
  }
  return FALSE;
  }
 */
function getBaseSKU($sku) { // ***USING***
        $resource_file = $GLOBALS['res_file'];
        $colours = getResourceFromXML($resource_file, 'alterego_colours');

        foreach ($colours as $colour) {
                $index = stripos($sku, $colour);
                if ($index !== FALSE) {
                        return substr($sku, 0, $index);
                }
        }

        $sizes = getResourceFromXML($resource_file, 'alterego_sizes');

        foreach ($sizes as $size) {
                $index = stripos($sku, $size);
                if ($index !== FALSE) {
                        return substr($sku, 0, $index);
                }
        }
        //try with first 8 characters to build sku
        return substr($sku, 0, 8);
}

function generateParentSKU($SKU) {
        // remove ()/ and replace with _
        // convert to all upper case
        // remove colour from end of string
//    $SKU = mb_strtolower($SKU, 'UTF-8');

        $SKU = str_replace('?', '_', $SKU);
        $SKU = str_replace('(', '_', $SKU);
        $SKU = str_replace(')', '_', $SKU);
        $SKU = str_replace('-', '_', $SKU);
        $SKU = str_replace('/', '_', $SKU);
        $SKU = str_replace(' ', '_', $SKU);
        $SKU = str_replace("'", '_', $SKU);
        $SKU = str_replace('__', '_', $SKU);

        if (stripos($SKU, "fleur") > 0) {
                $SKU = remove_accents($SKU);
        }

//    $b = array("á", "é", "í", "ó", "ú", "n");
//    $c = array("a", "e", "i", "o", "u", "n");
//    $SKU = str_replace($b, $c, $SKU);

        $SKU = strtoupper($SKU);

        $SKU = getBaseName($SKU);

        return $SKU;
}

function remove_accents($str, $utf8 = true) {
        $str = (string) $str;
        if (is_null($utf8)) {
                if (!function_exists('mb_detect_encoding')) {
                        $utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
                } else {
                        $length = strlen($str);
                        $utf8 = true;
                        for ($i = 0; $i < $length; $i++) {
                                $c = ord($str[$i]);
                                if ($c < 0x80)
                                        $n = 0;# 0bbbbbbb
                                elseif (($c & 0xE0) == 0xC0)
                                        $n = 1;# 110bbbbb
                                elseif (($c & 0xF0) == 0xE0)
                                        $n = 2;# 1110bbbb
                                elseif (($c & 0xF8) == 0xF0)
                                        $n = 3;# 11110bbb
                                elseif (($c & 0xFC) == 0xF8)
                                        $n = 4;# 111110bb
                                elseif (($c & 0xFE) == 0xFC)
                                        $n = 5;# 1111110b
                                else
                                        return false;# Does not match any model
                                for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                                        if (( ++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                                                $utf8 = false;
                                                break;
                                        }
                                }
                        }
                }
        }

        if (!$utf8)
                $str = utf8_encode($str);
        $transliteration = array(
            'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
            'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
            'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
            'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
            'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
            'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
            'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
            'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
            'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
            'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
            'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
            'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
            'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
            'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
            'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
            'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
            'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
            'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
            'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
            'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
            'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
            'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
            'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
            'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
            'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
            'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
            'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
            'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
            'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
            'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
            'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
            'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
            'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
            'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
            'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
            'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
            'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
            'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
            'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
            'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
            'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
            'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
            'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
            'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
            'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
            'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
            'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
            'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
            'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
            'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
            'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
            'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
            'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
            'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
            'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
            'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
            'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
            'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
            'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
            'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
            'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
            'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
            'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
            'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
            'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
            'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
            'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
            'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
            'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
            'Ж' => 'Z', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
            'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
            'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'K', 'Ц' => 'T', 'Ч' => 'C',
            'Ш' => 'S', 'Щ' => 'S', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'Y', 'Я' => 'Y',
            'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E',
            'ё' => 'E', 'ж' => 'Z', 'з' => 'Z', 'и' => 'I', 'й' => 'I', 'к' => 'K',
            'л' => 'L', 'м' => 'M', 'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R',
            'с' => 'S', 'т' => 'T', 'у' => 'U', 'ф' => 'F', 'х' => 'K', 'ц' => 'T',
            'ч' => 'C', 'ш' => 'S', 'щ' => 'S', 'ы' => 'Y', 'э' => 'E', 'ю' => 'Y',
            'я' => 'Y', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
            'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
            'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
            'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
            'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
            'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
            'ჯ' => 'j', 'ჰ' => 'h', '\ufffd' => 'e'
        );
        $str = str_replace(array_keys($transliteration), array_values($transliteration), $str);
        return $str;
}

function spaces_to_underscore($array, $change_keys = FALSE) {

        if ($change_keys) {
                foreach ($array as $key => $value) {
                        $key = str_replace('?', '', $key);
                        $key = str_replace('(', '', $key);
                        $key = str_replace(')', '', $key);
                        $key = str_replace('-', '_', $key);
                        $new_array[str_replace(' ', '_', $key)] = $value;
                }
        } else {
                foreach ($array as $key => $value) {
                        $value = str_replace('?', '', $value);
                        $value = str_replace('(', '', $value);
                        $value = str_replace(')', '', $value);
                        $key = str_replace('-', '_', $key);
                        $array[$key] = str_replace(' ', '_', $value);
                }
        }
        return $new_array;
}

function showProducts($start_row, $items_per_page, $filters = FALSE) { // ***USING***
        if ($start_row < 0) {
                $start_row = 0;
        }
        if (!isset($_SESSION)) {
                session_start();
        }
        $table_name = $_SESSION['table_name'];

        $conn = openDB('rwk_productchooserdb');

//        $data = getProductData($conn, $table_name, $start_row, $items_per_page, $filters);
        $data = getGroupedProductData($conn, $table_name, $start_row, $items_per_page, $filters);
        $html = product_data_to_html($data);

        return implode(' ', $html);
}

function product_data_to_html($data) { // ***USING***
        $html_array[] = '<div id="product_data" class="base-layer">';

        foreach ($data as $group) {
                
                $group_id = $group['group_id'];

                $html_array[] = '<div class="product_box">';

                $html_array[] = '<div class="left-box">';
                $html_array[] = '<div class="table-row">';
                $html_array[] = '<div id="carousel_' . $group_id . '" class="carousel">';
                $html_array[] = '<input id="left_btn_' . $group_id . '" type="button" value="<" class="left-button image-slide-btn" name="left_btn_' . $group_id . '" data-id="' . $group_id . '" data-direction="left"/>';
                $html_array[] = '<input id="right_btn_' . $group_id . '" type="button" value=">" class="right-button image-slide-btn" name="right_btn_' . $group_id . '" data-id="' . $group_id . '" data-direction="right"/>';
                $images = explode(',', $group['images']);
                $count = count($images);
                $html_array[] = '<ul data-count="' . $count . '">';
                $index = 1;
                foreach ($images as $image) {
                        $html_array[] = '<li><img id="image_' . $group_id . '_' . $index . '" class="image" src="' . $image . '"></li>';
                        $index++;
                }
                $html_array[] = '</ul>';
                $html_array[] = '</div>'; // carousel
                $html_array[] = '</div>'; //table-row
                $html_array[] = '</div>'; // left-box

                $html_array[] = '<div class="right-box">';

                $html_array[] = '<div class="table-row">';

                foreach ($group['products'] as $product) {
                        $html_array[] = '<div class="info-box">';

                        $html_array[] = '<div class="table-row">';
                        $html_array[] = '<span class="left-span">Name : <label id="name_' . $product['Product_ID'] . '">' . $product['Name'] . '</label></span>';
                        $html_array[] = '<span class="left-span">Brand : <label id="brand_' . $product['Product_ID'] . '">' . $product['Brand'] . '</label></span>';
                        $html_array[] = '</div>'; // table-row

                        $html_array[] = '<div class="table-row">';
                        $html_array[] = '<span class="left-span">Price : £<label id="price_' . $product['Product_ID'] . '">' . $product['Price_RRP'] . '</label></span>';
                        $html_array[] = '<span class="left-span">Trade Price : £<label id="trade_price_' . $product['Product_ID'] . '">' . $product['Trade_Price'] . '</label></span>';

                        if ($product['Selling'] == TRUE) {
                                $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $product['Product_ID'] . '" class="selling_checkbox" type="checkbox"  data-id="' . $product['Product_ID'] . '" checked></span>';
                        } else {
                                $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $product['Product_ID'] . '" class="selling_checkbox"  type="checkbox"  data-id="' . $product['Product_ID'] . '"></span>';
                        }
                        $html_array[] = '</div>'; // table-row

                        $html_array[] = '<div class="table-row">';
                        $html_array[] = '<span class="left-span">Product ID : <label id="product_id_' . $product['Product_ID'] . '">' . $product['Product_ID'] . '</label></span>';
                        $html_array[] = '<span class="left-span">SKU : <label id="sku_' . $product['Product_ID'] . '">' . $product['SKU'] . '</label></span>';
                        $html_array[] = '</div>'; // table-row

                        $html_array[] = '<div class="table-row">';
                        $html_array[] = '<span class="left-span">Variations</span>';
                        $html_array[] = '<span class="left-span">Size : <label id="size_' . $product['Product_ID'] . '">' . $product['Size'] . '</label></span>';
                        $html_array[] = '<span class="left-span">Colour : <label id="colour_' . $product['Product_ID'] . '">' . $product['Colour'] . '</label></span>';
                        $html_array[] = '</div>'; // table-row

                        $html_array[] = '<div class="table-row">';
                        //new   
                        $html_array[] = '<div class="popover__wrapper">';
                        $html_array[] = '<span class="left-span">Stock Type : <label id="stock_type_' . $product['Product_ID'] . '">' . $product['Stock_Type'] . '</label></span>';
                        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';

                        $html_array[] = '<div class="popover__wrapper">';
                        $html_array[] = '<span class="left-span">Stock Level : <label id="stock_level_' . $product['Product_ID'] . '">' . $product['Stock_Level'] . '</label></span>';
                        $html_array[] = '<div class="popover__content"><p class="popover__message">Green – this item is in stock<br>Amber – this item is in stock, but stock levels are low<br>Red – this item is out of stock or sold out<br>Blue – this item is pre-order continuity (available all year) or pre-order fashion</p></div></div>';
                        // end
//        $html_array[] = '<span class="left-span">Stock Type : <label id="stock_type_' . $product['Product_ID'] . '">' . $product['Stock_Type'] . '</label></span>';
//        $html_array[] = '<span class="left-span">Stock Level : <label id="stock_level_' . $product['Product_ID'] . '">' . $product['Stock_Level'] . '</label></span>';
                        $html_array[] = '</div>'; // table-row

                        $html_array[] = '</div>'; //info-box
                }
                $html_array[] = '</div>'; // table-row

                $html_array[] = '<div class="table-row">';
                $html_array[] = '<p class="left-span"><label id="description_' . $product['Product_ID'] . '">' . $product['Description'] . '</label></p>';
                $html_array[] = '</div>'; // table-row

                $html_array[] = '</div>'; // right-box
                $html_array[] = '</div>'; // product-box
        }
        $html_array[] = '</div>'; // base-layer

        return $html_array;
}

/*
  function product_data_to_html_OLD2($data) {
  $html_array[] = '<div id=\'product_data\' class="base-layer">';
  foreach ($data as $array) {
  $keys = array_keys($array);
  $count = count($array);
  $first_variant = $array[$keys[0]];
  $first_id = $array[$keys[0]]['Product_ID'];
  $html_array[] = '<div class="product_box">';
  $html_array[] = '<div class="left-box">';
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<div id="carousel_' . $first_id . '" class="carousel">';
  $html_array[] = '<input id="left_btn_' . $first_id . '" type="button" value="<" class="left-button image-slide-btn" name="left_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="left"/>';
  $html_array[] = '<input id="right_btn_' . $first_id . '" type="button" value=">" class="right-button image-slide-btn" name="right_btn_' . $first_id . '" data-id="' . $first_id . '" data-direction="right"/>';
  $html_array[] = '<ul data-count="' . $count . '">';
  if ($count === 0) {
  //use 'image coming soon placeholder
  $no_image = './image_coming_soon.jpg';
  $html_array[] = '<li><img id="image_none" class="image" src="' . $no_image . '" style="margin-left:250;"></li>';
  } else {
  foreach ($array as $variant) {
  $html_array[] = '<li><img id="image_' . $variant['Product_ID'] . '" class="image" src="' . $variant['Image'] . '"></li>';
  }
  }
  $html_array[] = '</ul>';
  $html_array[] = '</div>'; // carousel
  $html_array[] = '</div>'; //table-row
  $html_array[] = '</div>'; // left-box
  $html_array[] = '<div class="right-box">';
  $html_array[] = '<div class="table-row">';
  foreach ($array as $variant) {
  $html_array[] = '<div class="info-box">';
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<span class="left-span">Name : <label id="name_' . $variant['Product_ID'] . '">' . $variant['Name'] . '</label></span>';
  $html_array[] = '</div>'; // table-row
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<span class="left-span">Price : £<label id="price_' . $variant['Product_ID'] . '">' . $variant['Price_RRP'] . '</label></span>';
  $html_array[] = '<span class="left-span">Trade Price : £<label id="trade_price_' . $variant['Product_ID'] . '">' . $variant['Trade_Price'] . '</label></span>';
  if ($array[$keys[0]]['Selling'] == TRUE) {
  $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $variant['Product_ID'] . '" class="selling_checkbox" type="checkbox"  data-id="' . $variant['Product_ID'] . '" checked></span>';
  } else {
  $html_array[] = '<span class="left-span">Selling : <input id="selling_' . $variant['Product_ID'] . '" class="selling_checkbox"  type="checkbox"  data-id="' . $variant['Product_ID'] . '"></span>';
  }
  $html_array[] = '</div>'; // table-row
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<span class="left-span">Product ID : <label id="product_id_' . $variant['Product_ID'] . '">' . $variant['Product_ID'] . '</label></span>';
  $html_array[] = '<span class="left-span">SKU : <label id="sku_' . $variant['Product_ID'] . '">' . $variant['SKU'] . '</label></span>';
  $html_array[] = '</div>'; // table-row
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<span class="left-span">Variations</span>';
  $html_array[] = '<span class="left-span">Size : <label id="size_' . $variant['Product_ID'] . '">' . $variant['Size'] . '</label></span>';
  $html_array[] = '<span class="left-span">Colour : <label id="colour_' . $variant['Product_ID'] . '">' . $variant['Colour'] . '</label></span>';
  $html_array[] = '</div>'; // table-row
  $html_array[] = '</div>'; //info-box
  }
  $html_array[] = '</div>'; // table-row
  $html_array[] = '<div class="table-row">';
  $html_array[] = '<p class="left-span"><label id="description_' . $first_id . '">' . $first_variant['Description'] . '</label></p>';
  $html_array[] = '</div>'; // table-row
  $html_array[] = '</div>'; // right-box
  $html_array[] = '</div>'; // product-box
  }
  $html_array[] = '</div>'; // base-layer
  return $html_array;
  }
 */
function updateSelling() { // ***USING***
        if (!isset($_SESSION)) {
                session_start();
        }
        $table_name = $_SESSION['table_name'];
        $selling_list = $_POST['selling'];

        $conn = openDB('rwk_productchooserdb');
        updateSellingDB($conn, $table_name, $selling_list);
}

function exportToCSV($wholesaler) {

        $ini_val = ini_get('upload_tmp_dir');
        $temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
        if (!isset($_SESSION)) {
                session_start();
        }
        $table_name = $_SESSION['table_name'];

        $conn = openDB('rwk_productchooserdb');

        $file_url = $temp_path . '/' . $table_name . '.csv';

        $row = 0;
        $max_id = get_largest_id($table_name);
        $group_id_base = get_group_id_base($max_id);

        if (($handle = fopen("$file_url", "w")) !== FALSE) {

                $woo_headers = array_keys(getResourceFromXML($GLOBALS['res_file'], $wholesaler . '_map', 'map'));

                $result = fputcsv($handle, $woo_headers);

                $groups = getGroups($conn, $table_name);
                foreach ($groups as $group => $value) {
//            $result = getProductsBySKU($group, $table_name);
                        $result = getProductsByProductCode($group, $table_name);
                        if (!empty($result)) {
                                $groupArray = createGroup($result, $wholesaler, $group);
                                foreach ($groupArray as $product) {
                                        fputcsv($handle, $product);
                                }
                        }
                }
        }

        fclose($handle);
        return TRUE;
}

function exportToWP() {

        $ini_val = ini_get('upload_tmp_dir');
        $temp_path = $ini_val ? $ini_val : sys_get_temp_dir();
        if (!isset($_SESSION)) {
                session_start();
        }
        $table_name = $_SESSION['table_name'];

        $conn = openDB('rwk_productchooserdb');

        $sql = "CREATE TABLE  rwk_seduce.alterego SELECT * FROM rwk_productchooserdb.alterego_first_35";
        $results = $conn->query($sql);
        if ($results === FALSE) {
                return array("mysqli_error" => $conn->error);
        } else {
                return TRUE;
        }
}

function createGroup($result, $wholesaler, $group) {

        $map = getResourceFromXML($GLOBALS['res_file'], $wholesaler . '_map', "map", TRUE);
        $group_added = FALSE;
        $num_products = count($result);

        foreach ($map as $wookey => $woovalue) {

                if (stripos($wookey, 'attribute') === FALSE) {
                        $new_array[0][$wookey] = $woovalue === "" ? "" : $result[0][$woovalue];
                        for ($i = 1; $i <= $num_products; $i++) {
                                $new_array[$i][$wookey] = $woovalue === "" ? "" : $result[$i - 1][$woovalue];
                        }
                } else {
                        if ($woovalue !== "") {
                                $num = intval(preg_replace('/[^0-9]+/', '', $wookey), 10);
                                $new_array[0][$wookey] = $woovalue;
                                for ($i = 1; $i <= $num_products; $i++) {
                                        $new_array[$i][$wookey] = $woovalue;
                                }
                                for ($i = 1; $i <= $num_products; $i++) {
                                        $new_array[0]['Attribute ' . $num . ' value(s)'] = $new_array[0]['Attribute ' . $num . ' value(s)'] . ',' . $result[$i - 1][$woovalue];
                                        $new_array[$i]['Attribute ' . $num . ' value(s)'] = $result[$i - 1][$woovalue];

                                        $new_array[$i]['Attribute ' . $num . ' visible'] = 1;
                                        $new_array[$i]['Attribute ' . $num . ' global'] = 1;
                                        $new_array[$i]['Attribute ' . $num . ' default'] = "";
                                }
                                $new_array[0]['Attribute ' . $num . ' value(s)'] = ltrim($new_array[0]['Attribute ' . $num . ' value(s)'], ',');
                                $new_array[0]['Attribute ' . $num . ' visible'] = 1;
                                $new_array[0]['Attribute ' . $num . ' global'] = 1;
                                $new_array[0]['Attribute ' . $num . ' default'] = $result[0][$woovalue];
                        }
                }
        }
        $new_array[0]['Name'] = getBaseName($new_array[0]['Name']);
        $new_array[0]['Type'] = 'variable';
        $new_array[0]['Published'] = '1';
        $new_array[0]['Is featured?'] = '0';
        $new_array[0]['Visibility in catalogue'] = 'visible';
        $new_array[0]['Backorders allowed?'] = '0';
        $new_array[0]['Sold individually?'] = '0';
        $new_array[0]['Allow customer reviews?'] = '0';
        $new_array[0]['Position'] = '0';
        $new_array[0]['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($result[0]['Image'], '.');
        $new_array[0]['SKU'] = $group;

        for ($i = 1; $i <= $num_products; $i++) {
                $new_array[$i]['Type'] = 'variation';
                $new_array[$i]['Published'] = '1';
                $new_array[$i]['Is featured?'] = '0';
                $new_array[$i]['Visibility in catalogue'] = 'visible';
                $new_array[$i]['Backorders allowed?'] = '0';
                $new_array[$i]['Sold individually?'] = '0';
                $new_array[$i]['Allow customer reviews?'] = '0';
                $new_array[$i]['Position'] = '0';
                $new_array[$i]['Images'] = 'http://localhost/ImagesFromCSV' . ltrim($result[$i - 1]['Image'], '.');
                $new_array[$i]['SKU'] = str_replace('/', '', $new_array[$i]['SKU']);
                $new_array[$i]['Parent'] = $group;
        }

        return $new_array;
}

function generateFilters() { // ***USING***
        $html_array[] = 'All : <input id="all" name="all" value="All" class="filter_type" type="checkbox" checked>';

        $html_array[] = 'Stock Green : <input name="stock_green" value="Stock_Level=green" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Stock Amber : <input name="stock_amber" value="Stock_Level=amber" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Stock Red : <input name="stock_red" value="Stock_Level=red" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Discontinued : <input name="stock_discontinued" value="Stock_Type =Discontinued" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Pre Order : <input name="stock_pre_order" value="Stock_Type=Pre-Order Continuity" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Stock Line : <input name="stock_line" value="Stock_Type=Stock Line" class="filter_type" type="checkbox" unchecked>';

        $html_array[] = 'Brand Bassaya : <input name="brand" value="Brand=Bassaya" class="filter_type" type="checkbox" unchecked>';

        echo implode(' ', $html_array);
}

function generateFiltersOLD() { // ***USING***
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'All : <input id="all" name="all" value="All" class="filter_type" type="checkbox" checked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Stock Green : <input name="stock_green" value="Stock_Level=green" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Stock Amber : <input name="stock_amber" value="Stock_Level=amber" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Stock Red : <input name="stock_red" value="Stock_Level=red" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Discontinued : <input name="stock_discontinued" value="Stock_Type =Discontinued" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Pre Order : <input name="stock_pre_order" value="Stock_Type=Pre-Order Continuity" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Stock Line : <input name="stock_line" value="Stock_Type=Stock Line" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';
        $html_array[] = '<div class="popover__wrapper">';
        $html_array[] = 'Brand Bassaya : <input name="brand" value="Brand=Bassaya" class="filter_type" type="checkbox" unchecked>';
        $html_array[] = '<div class="popover__content"><p class="popover__message">Stock Lines are available all year round – this is the majority of our products.</p></div></div>';

        echo implode(' ', $html_array);
}

function create_progress() {
        // First create our basic CSS that will control
        // the look of this bar:
        echo "
<style>
#progress_text {
  position: absolute;
  top: 100px;
  left: 50%;
  margin: 0px 0px 0px -150px;
  font-size: 18px;
  text-align: center;
  width: 300px;
}
  #progress_barbox_a {
  position: absolute;
  top: 130px;
  left: 50%;
  margin: 0px 0px 0px -160px;
  width: 304px;
  height: 24px;
  background-color: black;
}
.per {
  position: absolute;
  top: 130px;
  font-size: 18px;
  left: 50%;
  margin: 1px 0px 0px 150px;
  background-color: #FFFFFF;
}

.bar {
  position: absolute;
  top: 132px;
  left: 50%;
  margin: 0px 0px 0px -158px;
  width: 0px;
  height: 20px;
  background-color: #0099FF;
}

.blank {
  background-color: white;
  width: 300px;
}
</style>
";

        // Now output the basic, initial, XHTML that
        // will be overwritten later:
        echo "
<div id='progress_text'>Script Progress</div>
<div id='progress_barbox_a'></div>
<div class='bar blank'></div>
<div class='per'>0%</div>
";

        // Ensure that this gets to the screen
        // immediately:
        flush();
}

// A function that you can pass a percentage as
// a whole number and it will generate the
// appropriate new div's to overlay the
// current ones:

function update_progress($percent) {
        // First let's recreate the percent with
        // the new one:
        echo "<div class='per'>{$percent}
    %</div>\n";

        // Now, output a new 'bar', forcing its width
        // to 3 times the percent, since we have
        // defined the percent bar to be at
        // 300 pixels wide.
        echo "<div class='bar' style='width: ",
        $percent * 3, "px'></div>\n";

        // Now, again, force this to be
        // immediately displayed:
        flush();
}

function compareFiles($file_a, $file_b) {

        if (filesize($file_a) == filesize($file_b)) {
                $fp_a = fopen($file_a, 'rb');
                $fp_b = fopen($file_b, 'rb');

                while (($bytes_a = fread($fp_a, 4096)) !== false) {
                        $bytes_b = fread($fp_b, 4096);
                        if ($bytes_a !== $bytes_b) {
                                fclose($fp_a);
                                fclose($fp_b);
                                return false;
                        }
                }

                fclose($fp_a);
                fclose($fp_b);

                return true;
        }

        return false;
}

function splitCats($input) {

        $cat_array = [];
//        $search = array(' ', ';');
//        $replace = ',';
//
//        $result = str_replace($search, $replace, $input);
//        $result_array = explode(',', $result);

        $result_array = explode(',', $input);

//        $transliteration = array('babydolls' => 'babydoll', 'dresses' => 'dress', );
        foreach ($result_array as $cat) {
                //maybe too complicated!
//                $cat = str_replace(array_keys($transliteration), array_values($transliteration), strtolower($cat));
                $cat = str_replace('&', 'and', $cat);
                if (strtolower($cat) !== 'dress') {
                        $cat = ucfirst(rtrim($cat, 's'));
                        if ($cat === 'Dresse') {
                                $cat = 'Dress';
                        }
                }
                if (!in_array($cat, $cat_array)) {
                        $cat_array[] = $cat;
                }
        }
        return $cat_array;
}
