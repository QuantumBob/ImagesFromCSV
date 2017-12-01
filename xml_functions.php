<?php

function getItemFromXML($reader) {

    $node = new SimpleXMLElement($reader->readOuterXML());
    foreach ($node->children() as $child) {
        if (strpos($child->getName(), "image") !== FALSE) {

            $image_fullname = getImageFromWeb($child);
            $array[$child->getName()] = $image_fullname;
        } else {
            $array[$child->getName()] = $child;
        }
    }
    return $array;
}

function getIElementsFromXML($reader, $resource) {

    while ($reader->read() && $reader->name !== $resource);

    $node = new SimpleXMLElement($reader->readOuterXML());
    foreach ($node->children() as $child) {
//        $array[] = $child->getName();
        $array[] = (string) $child;
    }
    return($array);
}

function openXMLFile() {

    $reader = new XMLReader;
    $reader->open("D:\Documents\work\Seduce\Alterego_first_few.xml");
    return $reader;
}

function getResourceFromXML($file_path, $resource, $attr = "", $add_empty = FALSE) {

    $reader = new XMLReader;
    $reader->open($file_path);

    // check first node is <resources>
    if ($reader->read() && $reader->name !== 'resources') {
        die('Not a resource file');
    }
    // move to first instance of $resouce node
    while ($reader->read() && $reader->name !== $resource);
    // loop through all node of type $resource
    while ($reader->name === $resource) {

        $node = new SimpleXMLElement($reader->readOuterXML());
        foreach ($node->children() as $child) {
            if (!empty($attr) && isset($child[$attr])){
//                $array[] =[(string) $child, (string) $child[$attr]];
                $array[(string) $child] = (string) $child[$attr];
            } elseif (!empty($attr) && !isset($child[$attr])) {
//            $array[] = [(string) $child,''];
                $array[(string) $child] = '';
            } else {
                $array[] = (string) $child;
            }
        }
        $reader->next($resource);
    }
    $reader->close();

    return $array;
}
