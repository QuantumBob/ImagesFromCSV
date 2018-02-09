<?php

$dir = new DirectoryIterator($_FILES['Filename']);
foreach($dir as $fileinfo){
    $ext = pathinfo($fileinfo->getPath(),PATHINFO_EXTENSION  );
    $filename = $fileinfo->getFilename();
    $pattern = "/_\d" . $ext . "/";
    preg_match_all($pattern, $filename,$matches,PREG_SET_ORDER);
    $count = count($matches);
    $result = end($matches[$count-1]);
    $basename= str_replace($result, "", $filename);
    if (file_exists($basename)){
        $dim1 = getimagesize($basename);
        $dim2 = getimagesize($filename);
    }
}

