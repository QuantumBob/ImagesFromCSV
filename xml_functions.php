<?php
/**
 * 
 * @param type $file_path
 * @param type $resource
 * @param type $attr
 * @param type $add_empty
 * @return type $array
 */
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
