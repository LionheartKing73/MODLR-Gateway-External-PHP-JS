<?php
class StringResource{
	private $string = array();
	public function StringResource($path){
		global $lang;
		if($lang == null){
			$file = $path . 'strings.xml';
			if(file_exists($file))
				$xml = simplexml_load_file($file);
		}else{
			$file = $path . 'strings_'.$lang.'.xml';
			if(file_exists($file)){
				$xml = simplexml_load_file($file);
			}else{
				$file = $path . 'strings.xml';
				if(file_exists($file))
					$xml = simplexml_load_file($file);
			}
		}
		
		if($xml){
			foreach($xml->children() as $child) {
				if($child->getName() == 'string'){
					$attribute = $child->attributes();
					$key = (String) $attribute['name'];
					$this->string[$key] = (String) $child;
				}
			}
		}
	}
	
	public function Get($key){
		return $this->string[$key];
	}
}
