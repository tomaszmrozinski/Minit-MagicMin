<?php
/*
Plugin Name: Minit MagicMin
Plugin URI: https://github.com/tomaszmrozinski/minit-magic-min
Description: Adds Magic Min Compressor minification to the Minit plugin by Kaspars Dambis. Based on MagicMin class by Bennett Stone (www.phpdevtips.com) and Minit YUI class by Bjørn Johansen (https://github.com/bjornjohansen/minit-yui) 
Version: 0.0.1
Author: Tomasz Mrozinski
Author URI: mroznski.net
*/

new Minit_Magic_Min;

class Minit_Magic_Min{
	
	private $content;
	private $type;

        public function __construct(){

        	add_filter('minit-content-css', array($this, 'minit_content_css'), 11, 3);
        	add_filter('minit-content-js', array($this, 'minit_content_js'), 11, 3);

        }

        public function minit_content_css($content = '', $object = '', $script = ''){
        	
        	return $this->minify($content,'css');
        	
        }

        public function minit_content_js($content = '', $object = '', $script = ''){
        	
        	return $this->minify($content, 'js');
        	
        }
        
        private function minify($content = '',$type = 'css'){
        	
        	if(strlen($content)){

        		$_minified = $this->minify_content($content, $type);
	                if(strlen($_minified))$content = $_minified;
                
        	}    

        	return $content;
        	
        }
        
        private function minify_content($content, $type = 'css'){
        
              	$this->content = $content;
        	$this->type = $type;
        
        	 if($this->type == 'js'){
        		
        		//Build the data array
        		$data = array(
	        		'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
	        		'output_format' => 'text',
	        		'output_info' => 'compiled_code',
	        		'js_code' => urlencode($this->content)
        		);
        
        		//Compile it into a post compatible format
        		$fields_string = '';
        		foreach($data as $key => $value)$fields_string .= $key . '=' . $value . '&';
        		
        		rtrim($fields_string, '&');
        
        		//Initiate and execute the curl request
        		$h = curl_init();
        		curl_setopt($h, CURLOPT_URL, 'http://closure-compiler.appspot.com/compile');
        		curl_setopt($h, CURLOPT_POST, true);
        		curl_setopt($h, CURLOPT_POSTFIELDS, $fields_string);
        		curl_setopt($h, CURLOPT_HEADER, false );
        		curl_setopt($h, CURLOPT_RETURNTRANSFER, 1);
        		$result = curl_exec($h);
        		$this->content = $result;
        		
        		//close connection
        		curl_close($h);
        		
        	}else{//$this->type == 'css'
        	
        		/* remove comments */
        		$this->content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->content);
        		/* remove tabs, spaces, newlines, etc. */
        		$this->content = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $this->content);
        		/* remove other spaces before/after ; */
        		$this->content = preg_replace(array('(( )+{)','({( )+)'), '{', $this->content);
        		$this->content = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $this->content);
        		$this->content = preg_replace(array('(;( )+)','(( )+;)'), ';', $this->content);
        
        	} //end $this->type == 'css'
        	
        	
		return $this->content;
        
        }
}
