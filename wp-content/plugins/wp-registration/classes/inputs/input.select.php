<?php
/*
 * Followig class handling select input control and their
* dependencies. Do not make changes in code
* Create on: 9 November, 2013
*/

class NM_Select extends NM_Inputs_wpregisration{
	
	/*
	 * input control settings
	 */
	var $title, $desc, $settings;
	
	/*
	 * this var is pouplated with current plugin meta
	*/
	var $plugin_meta;
	
	function __construct(){
		
		$this -> plugin_meta = wpregistration_get_plugin_meta();
		
		$this -> title 		= __ ( 'Select-box Input', 'wp-registration' );
		$this -> desc		= __ ( 'regular select-box input', 'wp-registration' );
		$this -> settings	= self::get_settings();
		
	}
	
	
	
	
	private function get_settings(){
		
		return array (
		'title' => array (
				'type' => 'text',
				'title' => __ ( 'Title', 'wp-registration' ),
				'desc' => __ ( 'It will be shown as field label', 'wp-registration' ) 
		),
		'data_name' => array (
				'type' => 'text',
				'title' => __ ( 'Data name', 'wp-registration' ),
				'desc' => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'wp-registration' ) 
		),
		'description' => array (
				'type' => 'text',
				'title' => __ ( 'Description', 'wp-registration' ),
				'desc' => __ ( 'Small description, it will be diplay near name title.', 'wp-registration' ) 
		),
		'error_message' => array (
				'type' => 'text',
				'title' => __ ( 'Error message', 'wp-registration' ),
				'desc' => __ ( 'Insert the error message for validation.', 'wp-registration' ) 
		),
		
		'options' => array (
				'type' => 'textarea',
				'title' => __ ( 'Add options', 'wp-registration' ),
				'desc' => __ ( 'Type each option per line', 'wp-registration' ) 
		),
		'selected' => array (
				'type' => 'text',
				'title' => __ ( 'Selected option', 'wp-registration' ),
				'desc' => __ ( 'Type option name (given above) if you want already selected.', 'wp-registration' ) 
		),
		
		'required' => array (
				'type' => 'checkbox',
				'title' => __ ( 'Required', 'wp-registration' ),
				'desc' => __ ( 'Select this if it must be required.', 'wp-registration' ) 
		),
		
		'class' => array (
				'type' => 'text',
				'title' => __ ( 'Class', 'wp-registration' ),
				'desc' => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', 'wp-registration' ) 
		),
		'width' => array (
				'type' => 'text',
				'title' => __ ( 'Width', 'wp-registration' ),
				'desc' => __ ( 'Type field width in % e.g: 50%', 'wp-registration' ) 
		),
		
		);
	}
	
	
	/*
	 * @params: $options
	*/
	function render_input($args, $options="", $default=""){
		
		$_html = '<select ';
		
		foreach ($args as $attr => $value){
			
			$_html .= $attr.'="'.stripslashes( $value ).'"';
		}
		
		$_html .= '>';
		
		$_html .= '<option value="">'.__('Select option', $this -> plugin_meta['shortname']).'</option>';
		
		foreach($options as $opt)
		{
				
			$output = stripslashes(trim($opt));
				
			$_html .= '<option value="'.$opt.'" '. selected($default, $opt, false).'>';
			$_html .= $output;
			$_html .= '</option>';
		}
		
		$_html .= '</select>';
		
		echo $_html;
	}
}