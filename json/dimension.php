<?php
ini_set('post_max_size',"8M");
error_reporting(E_ALL);
include_once("../lib/lib.php");

$action = strtolower($_POST['action']);

if ($action == "retrieve_hierarchy"){
    $hierarchy_name = $_POST['h_name'];
    $str = "";
    if ($hierarchy_name == ""){
        $hierarchies = json_decode($_POST['hierarchies'], true)[0]['root'];		
        if (count($hierarchies) > 0){
            foreach ($hierarchies as $h){
                $str .= recursiveExtractHierarchy($h,'');
            }        
        }
    } else {
        $all_hierarchies = json_decode($_POST['hierarchies'], true);
        foreach ($all_hierarchies as $hier)
        {
            if ($hier['name'] == $hierarchy_name){
                break;
            }
        }
        $hier = $hier['root'];
        if (count($hier) > 0){
            foreach ($hier as $h){
                $str .= recursiveExtractHierarchy($h,'');
            }        
        }
    }
    echo $str;
} else if ($action == "hierarchy_update") {
    $hierarchyToEdit = $_POST['hierarchyToEdit'];
    $defaultHier = array();
    $defaultHier['root'] = [];
    $defaultHier['name'] = $hierarchyToEdit;
    $root = buildElementTree($_POST['elmArray']);
}


function recursiveExtractHierarchy($node, $indent){
    $type = "";
	if( $node['type'] == "S" ) 
		$type = "[S]";
	
    $str = $indent . $type . $node['name'] . '%0A';
        
    $children = $node['children'];
	foreach($children as $c) {
		$str .= recursiveExtractHierarchy($c,$indent.'&#9;');
	}	
	return $str;
}


function buildElementTree($elmArray) {
	$root = [];
	$parents = [];
	for($i=0;$i<count($elmArray);$i++) {
		$elm = $elmArray[$i];
		$type = "N";
		if( strtolower(substr($elm, strlen($elm)-3,3)) == "[s]" ) {
			$type = "S";
			$elm = substr($elm, 0,strlen($elm)-3);
		}
		if( strtolower(substr($elm, 0,3)) == "[s]" ) {
			$type = "S";
			$elm = substr($elm, 3,strlen($elm)-3);
		}
		
		$node = array();
		$node['name'] = trim($elm);
		$node['children'] = [];
		$node['type'] = $type;
		if( strlen($elm) > 0 ) {
			$tabs = preg_split('/[\t]/', $elm);
			while( $tabs[count($tabs)-1] == "") {	//trim off the trailing tabs
				echo "Tabss";
				$tabs = array_slice($tabs, count($tabs)-1);
			}
			if( count($tabs) > 1 ) {
				
				$children = $parents[count($tabs)-2]['children'];
				$children[count($children)] = $node;
				$parents[count($tabs)-2]['children'] = $children;
				
			} else {
				//add this element to the root array.
				$root[count($root)] = $node;
				
			}
			$parents[count($tabs)-1] = $node;
		}
	}
	return $parents;
}
