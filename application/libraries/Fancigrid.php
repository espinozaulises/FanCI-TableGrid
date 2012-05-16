<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Name:  Ci_datagrid
 *
 * Author: Ulises Vázquez Espinoza
 *
 * Created:  31.12.2011
 *
 * Description:
 *
 */

class Fancigrid
{

	//Parametros que pueden definirse en el controller.
	public $actions 	= array();	// Botones de acción de la tabla.
	public $columns 	= array(); 	// Arreglo que contiene las columnas y sus opciones
	public $extra_params= 0;		// Valores de las variables extra por default 
	public $grid_name	= 'fanCIgrid';
	public $my_segment	= 3;		// El segmento de la URI en el cual pasamos los valores de los filtros.
	public $pagination 	= array();	// Parametros de la librería pagination. base_url y per_page
	public $prim_key	= "id";		// Nombre del campo de clave primaria por default.
	public $prim_key_hide = true;	// Esconder/mostrar el id en el grid.
	public $segment		= "";
	public $sql_query	= array(); 	// Array que contiene los elementos de la consulta.
	public $sql_string  = ""; 		// Cadena de texto de la consulta sql 
	public $tableclass	= "table-striped table-bordered";
	public $url_site 	= "";
	
	public $like_string	= "";		// Cadena que contiene los datos del filtro/busqueda de texto.
	public $vars_url	= ""; 		// Variables extras pasadas en el url (después de $my_segment)
	public $uri 		= array(); 	// Arreglo que contiene los datos de la url actual.
	public $col_check   = TRUE;
	private $i_col_check;
	public $col_actions = TRUE;
	private $i_col_actions;

	public $autosum = FALSE;		// Mostrar la sumatoria de las columnas con formato money
	// Configuració de las constantes.
	const O_ASC			= "ASC";
	const O_DESC		= "DESC";
	const TABLE_BUTTONS	= 'ACCIONES';
	//Constantes de formato de datos
	const A_CENTER		= 'center';
	const A_RIGHT		= 'right';
	const TYPE_GENERAL 	= 'default';
	const TYPE_MONEY 	= 'money';
	const TYPE_LINK 	= 'link';
	const TYPE_CHECK 	= 'check';
	const TYPE_PERCENT 	= 'percent';
	const TYPE_DATE		= 'date';
	const TYPE_REPLACE	= 'replace';
	const ROW_CHECK 	= "<input class='dg_check_item' type='checkbox' name='dg_item[]' value='";
	const HEAD_CHECK	= "<input type='checkbox' class='dg_check_toggler'>";


	
	// Creando el constructor y pasamos los parametros del controlador
	public function __construct(){

		$this->CI =& get_instance();
		$this->CI->load->library(array('table','fancipager'));
		$this->CI->config->load('fancipager');
		$this->CI->config->load('fancigrid');
		$this->CI->load->helper(array('fanci_helper','security'));
		$this->CI->load->model("fanci_model");

		log_message('debug', "Ci_datagrid Class Initialized");

		$class = $this->tableclass;

	// TEMPLATE POR DEFAULT DE LA TABLA DEL GRID
		$tmpl = array ( 
		'table_open'  => "<table class='fancigrid $class' id='". $this->grid_name ."'>" 
		);

		// Cargamos el template por default
		$this->CI->table->set_template($tmpl);
		
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Array
	 *
	 * Formatea el array de manera que todo los campos tengan los campos data, sorter, filter y format.
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _set_headers()
	{
		$i = 0;
		$args = $this->columns;

		if ( $this->col_check )
		{
			$args[$i] = array(
						"data" 	 => self::HEAD_CHECK,
						"sorter" => FALSE,
						"filter" => FALSE,
						"format" => self::A_CENTER);
			$this->i_col_check = $i;
		}

		foreach($args as $key => $val)
		{
			
			if( ! isset($val["sorter"] ) || empty($val["sorter"]) )
			{
				$args[$key]["sorter"] = FALSE;
			}
			if( ! isset($val["filter"] ) || empty($val["filter"]) )
			{
				$args[$key]["filter"] = FALSE;
			}
			if( ! isset($val["format"] ) || empty($val["format"]) )
			{
				$args[$key]["format"] = self::TYPE_GENERAL;
			}
			$i++;
		}
		if( count($this->actions) > 0 )
		{
			foreach ($this->actions as $key) {
				$args[$i] = array(
						"data" 	 => '&nbsp;',
						"sorter" => FALSE,
						"filter" => FALSE,);
				$this->i_col_actions = $i++;

			}
		}
		$this->columns = $args;
	}

	function _get_headers()
	{
		$cols = "";
		$cont = 0;
		foreach ($this->columns as $key => $val)
		{
			$cols[$key] = $this->_get_sorter($key, $val);
			$cont++;
		}
		$this->cont_fields = $cont;
		return $cols;
	}

	function _get_sorter($key, $data)
	{
		if( $data["sorter"] ) {
			return '<a href="#">'.$data["data"].'</a>';
		}
		else {
			return $data["data"];
		}
	}	
	/*function _get_sorter($key, $data){
		
		if( $data["sorter"] ) {
			if( $key == $this->uri["field_order"] )	{
				if( $this->uri["order"] == "ASC" )
			} else {
				$icon = "ui-icon-triangle-2-n-s";
			}			
			$arrow = '<a href="#"><span class="ui-icon '.$icon.' dg-fright">Sorter</span></a>';
		}	else {
			$arrow = '';
		}
		return $data["data"].$arrow;
	}*/

	function _clear_id(  $str ){
		$str = str_replace(' ', '', $str);
		$str = str_replace('.', '', $str);
		$str = str_replace('/', '', $str);
		return $str;
	}
	/**
	 * Decode
	 **/
	function decode($string)
	{
		return base64_decode(strtr($string, '%.~', '+/='));
	}

	/**
	 * Encode
	 **/
	function encode($string)
	{
		return strtr(base64_encode($string), '+/=', '%.~');
	}

	/*
	 * BOTONES DE LAS TABLAS
	* */
	function table_buttons($uri='', $title='', $ico, $attributes=''){
		if($attributes=='')
			$attributes = array("class" => "ttipL", 'title' => $title); 

		return anchor($this->url_site.$uri , '<span class="sprite '.$ico.'"></span>', $attributes);
	}

	function _set_actions($id, $btn){

		$botones = "";
		
		if ( is_array($btn) ) {
			$opts = $this->CI->config->item('dg_'.$btn["button"]);
			$opts["id"] 	= 'dg_'.$btn["button"];
			$opts['icon'] 	= ( isset($btn['icon']) ) ? $btn['icon'] : $opts['icon'];
			$opts['title'] 	= ( isset($btn['title']) ) ? $btn['title'] : $opts['title'];
			$opts['url']	= ( isset($btn['url']) ) ? $btn['url'].'/'.$id : $this->url_site."/".$opts['url'].$id;
		}	else 	{
			$opts = $this->CI->config->item('dg_'.$btn);
			$opts["id"] = 'dg_'.$btn;
			$opts['url']	= $this->url_site.'/'.$opts['url'].$id;
		}

		$attr = array(
			"id" 	=> $opts["id"],
			"name" 	=> $opts["id"],
			"title" => $opts["title"],
			"class" => "grid-actions ttipL ".$opts["id"]
			);
		$botones .= anchor( $opts["url"], '<span class="fg-icon fg-icon-'. $opts["icon"].'"></span>', $attr);
		return array("data" => $botones, "class" => "fg-options" );
	}

	// --------------------------------------------------------------------

	/**
	 * Genera el data grid
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	public function _define_limits(){
		//Definiendo los límites
		$this->segment = $this->CI->uri->segment($this->my_segment);
		
		if( ! empty($this->segment) )
			$uri_params = explode("-", $this->segment);
		/* Si per_page ha sido configurado en el controlador... */
		if( ! empty($this->pagination["per_page"]) )
			$this->CI->config->set_item('per_page', $this->pagination["per_page"]);

		$per_page = $this->CI->config->item('per_page');
		
		if ( isset($uri_params[0]) )
			$this->sql_query["offset"] = $this->CI->security->xss_clean($this->CI->db->escape_str($uri_params[0]));
		else 
			$this->sql_query["offset"] 	= "0";
		
		if ( isset($uri_params[1]) ){
			$tmp = $this->CI->security->xss_clean($this->CI->db->escape_str($uri_params[1]));
			$tmp = ( $tmp == "all" ) ? "10000" : $tmp;
			$this->sql_query["limit"] = $tmp;
		}
		else
			$this->sql_query["limit"]  	= $per_page;
		
		if ( isset($uri_params[2]) )
		{
			$this->uri["field_order"] = $this->CI->security->xss_clean($this->CI->db->escape_str($uri_params[2]));
			$tmp = $this->columns[$uri_params[2]];
			$this->sql_query["field_order"] = $this->CI->security->xss_clean($this->CI->db->escape_str($tmp["field"]));
		}
		else 
		{
			$this->sql_query["field_order"] = $this->prim_key;
			$this->uri["field_order"] = 1;
			foreach ($this->columns as $key => $val)
			{
				if( is_array($val) )	{
					if( array_key_exists("order",$val) ){
						$this->sql_query["field_order"] = $val["field"];
						$this->sql_query["order"] = $val["order"];
						$this->uri["field_order"] = $key;
					}
				}
			}

		}
		
		if ( isset($uri_params[3]) )	{
			$tmp = $this->CI->security->xss_clean($this->CI->db->escape_str($uri_params[3]));
			if( $tmp == self::O_ASC || $tmp == self::O_DESC)
				$this->sql_query["order"] = $tmp;
			else
				$this->sql_query["order"] = self::O_ASC;
		}
		elseif ( ! isset($this->sql_query["order"]) ) {
			$this->sql_query["order"] = self::O_ASC;
		}
		
		if ( isset($uri_params[4]) ){
			if( $uri_params[4] == "none" )
			{
				$this->sql_query["like"] = "";
				$this->uri["like_string"] = "none";
		
			} else {
		
				$this->uri["like_string"] = $uri_params[4];
				$vars = explode(":", $uri_params[4]);
		
				$vars[0] = $this->columns[$vars[0]]["field"];
				$vars[1] = $this->CI->security->xss_clean($this->CI->db->escape_str($this->decode($vars[1])));
				$this->sql_query["like"] = $vars;
		
			}
		}
		else	{
			$this->sql_query["like"]	= "";
			$this->uri["like_string"] = "none";
		}
		
		if ( isset($uri_params[5]) ){
			if( $uri_params[5] == "none" )
			{
				$this->uri["vars_url"] = $this->extra_params;
				$this->sql_query["vars"] = $this->extra_params;
			}
			else
			{
				$vars = explode(":", $uri_params[5]);
				foreach ($vars as $key => $val)
				{
					$vars[$key] = $this->CI->security->xss_clean($this->CI->db->escape_str($val));
				}
				$this->sql_query["vars"] = $vars;
				$this->uri["vars_url"] = $uri_params[5];
			}
		}
		else
		{
			$this->sql_query["vars"] = $this->extra_params;
			$this->uri["vars_url"] = $this->extra_params;
		}
		// INICIALIZANDO los datos del arreglo que contiene los datos de la URL
		$this->uri["base_url"]  = $this->url_site;
		$this->uri["limit"]  	= $this->sql_query["limit"];
		$this->uri["offset"] 	= $this->sql_query["offset"];
		$this->uri["order"]		= $this->sql_query["order"];
		//$this->uri["like_string"]=$this->uri["like_string"];
		$this->uri["hash"]		= $this->sql_query["offset"]."-".$this->sql_query["limit"].'-'.$this->uri["field_order"].'-'.$this->uri["order"].'-'.$this->uri["like_string"].'-'.$this->uri["vars_url"];
		$this->uri["hash_init"]	= "0-".$this->sql_query["limit"]."-".$this->uri["field_order"].'-'.$this->uri["order"];
		//$this->uri["vars"]      = $this->uri["vars_url"];
	}


	// --------------------------------------------------------------------
	/**
	 * Inicializa las variables
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */

	public function initialize( $params = array() ){
		// Set parameters
		foreach ($params as $key => $value)
		{
			$this->$key = $value;
		}

		$this->_set_headers();
		$this->_define_limits();
		
		$this->CI->fanci_model->initialize( $this->sql_query );
		$this->sql_string = $this->CI->fanci_model->set_query( $this->sql_query );

	}

	// --------------------------------------------------------------------

	/**
	 * Genera el data grid
	 *
	 * @access	public
	 * @param	mixed
	 * @return	string
	 */
	public function generate() {
		//AGREGAMOS LOS ENCABEZADOS DE LA TABLA
		$this->CI->table->set_empty("&nbsp;"); 
		$this->CI->table->set_heading( $this->_get_headers() );

		//Realiza la consulta de los datos.
		$rows = $this->CI->fanci_model->select( $this->sql_string );

		// Inicia la magia: Agregando filas a la tabla.
		foreach( $rows as $key => $field ) {	
			$i = 0;
			$tmp_row = array();
			$id = $field[$this->prim_key];
			$totales = array();

			// ¿Ocultar primary key en el grid?
			if( $this->prim_key_hide ){
				unset( $field[$this->prim_key] );
			}

			// Agrego la columna de los checkbox si $col_check = true.
			if( $this->col_check )	{
				$tmp_row[$i] = array("data" => self::ROW_CHECK . $id. "' />", "class" =>"fg-select");
			}
			$i++;
			// Paso el resultado de la consulta a un array temporaL
			foreach ($this->columns as $cols) {
				if ( isset($cols["field"]) )	{ //Si no es el check
					$colField = $cols["field"];
					// PRUEBA
					$dt = $field[$colField];

					$tmp_row[$i] = $this->_parser_format( $cols["format"], $field[$colField] );
					
					if( $cols["format"] === 'money' ){
						if( isset($totales[$i]) )
							$totales[$i] += $field[$colField];
						else
							$totales[$i] = $field[$colField];
					} else {
						$totales[$i] = '&nbsp;';
					}
					$i++;
				}
			}
			// Agrego la columna de acciones si $col_actions = true.
			if( count($this->actions) > 0 )	{
				foreach ($this->actions as $btn) {
					$tmp_row[] = $this->_set_actions($id, $btn);
				}
			}

			$this->CI->table->row_id( $id );
			$this->CI->table->add_row($tmp_row);			
		}
		if( $this->autosum && isset($totales) ) {
			foreach ($totales as $key => $value) {
				if( is_numeric($value) ) {
					$monto = $this->_parser_format( "money", $value );
					$totales[$key] = array("data" => $monto, "class" => "totales" );
				}
			}
			$this->CI->table->add_row($totales);
		}

		$this->pagination['per_page'] = $this->uri["limit"];
		$this->pagination['total_rows'] = $this->CI->fanci_model->count_rows();
		$this->CI->fancipager->initialize($this->pagination);

		$footing = array("data" => $this->CI->fancipager->create_links($this->uri), "colspan" => $this->cont_fields-1, "class" => "pager");
		$this->CI->table->set_footing("",$footing);

		$div_params = '<div style="display:none">
						<div id = "url_site">'		.$this->url_site.'</div>
						<div id = "dg_url">'		.$this->pagination["base_url"].'</div>
						<div id = "dg_limit">'		.$this->uri["limit"].'</div>
						<div id = "dg_offset">'		.$this->uri["offset"].'</div>
						<div id = "dg_order">'		.$this->uri["field_order"].'</div>
						<div id = "dg_order_type">'	.$this->uri["order"].'</div>
						<div id = "dg_like_str">'	.$this->uri["like_string"].'</div>
						<div id = "dg_vars">'		.$this->uri["vars_url"].'</div>
						<div id = "dg_hash">'		.$this->uri["hash"].'</div>
						<div id = "dg_hash_init">'	.$this->uri["hash_init"].'</div>
					  </div>';
		return "<div id='fanCI-layout'>" . $this->CI->table->generate() . $div_params . "</div>";
	}

	private function _parser_format( $str_format, $data ){
		$function = $this->_parser_format_function( $str_format, ":{", "}" );
		if( is_array( $function ) ) {
			$format = strtolower( $function["format"] );
			$params = $function["params"];
		} else {
			$format = strtolower( $function );
			$params = "";
		}

		switch ( $format ) {
			case 'center':
				$content = array("data" => $data, "style" => 'text-align:center;');
				break;
			case 'date':
				//Recibe la fecha en formato YYYY-MM-DD
				date_default_timezone_set('America/Chicago'); //Evita errores timezone
				$phpdate = strtotime( $data );
				$params["format"] = ( isset($params["format"]) )? $params["format"]:"d-m-Y";
				// Si se ha definido un setlocale
				if( isset($params["setlocale"]) )
					setlocale(LC_ALL, $params["setlocale"]); 
				$fecha = date($params["format"], $phpdate);
				$content = array("data" => $fecha, "style" => 'text-align:center;');
				break;
			case 'link':
				$texto = ( isset($params["text"]) ) ? $params["text"]:$data;
				$content = '<a href="'.prep_url($data).'">'.$texto.'</a>';
				break;
			case 'money':
				$content = '<span class="fgLeft">$</span><span class="fgRight">'.number_format($data,2,'.',',').'</span>';
				break;
			case 'percent':
				$content = '<div class="percent"><div class="bar" style="width: '.$data.'%">'.$data.'%</div></div>';
				break;
			case 'replace':
				if( isset($params[$data]) )
					$content = $params[$data];
				else
					$content = $data;
				break;
			case 'right':
				$content = array("data" => $data, "style" => 'text-align:right;');
				break;
			case 'FAIL':
				$content = "Fail format (x_x)";
			default:
				$content = $data;
				break;
		}
		return $content;
	}


	private function _parser_format_function($str, $str_start, $str_end){
		if( strstr($str, ':{') ) {
			$params	= strstr($str, $str_start);
		  	
		  	$tmp_format = trim( str_replace($params, '', $str) );
		  	$tmp_params = trim( str_replace(":{", "{", $params) );

		  	if( !json_decode($tmp_params) ) {
		  		$data["format"] = "FAIL";
		  		$data["params"] = "";
		  	}	else 	{
		  		$data["format"] = $tmp_format;
		  		$data["params"] = object2array( json_decode($tmp_params) );
		  	}

	  	}	else 	{
	  		$data = $str;
	  	}
	  	return $data;
	}

	public function toolbar( $buttons = array() )
	{
		//foreach($buttons as $key => $val){
			//$this->load->helper("form");
			$input  = array(
					  'name'        => 'str_search',
					  'id'          => 'str_search',
					  'class'       => 'text search-query',
					  'style'		=> 'float:left; margin-right:5px;'
					);
			
			//$filtro .= "<span>Filtros:&nbsp;</span>";
		foreach($this->columns as $key => $val){
			if( $key <> $this->i_col_check AND $key <> $this->i_col_actions )	
				$select[$key] = $val["data"];
		}
			$filtro  = form_open($this->url_site."/".$this->uri["hash"],"id='form_filter'") . form_input($input)."&nbsp;";
			$filtro .= form_dropdown("dg_filter_by", $select ,'','id="dg_filter_by" class="style-select ttipDw" title="Buscar en el campo..."');
			$filtro .= action_button_icon("", "icon198",'ttipDw left','submit', "id='btool-search' title='Buscar'");
			$filtro .= action_button_icon("", "icon83",'ttipDw right','','id="btool-clear" title="Limpiar la busqueda"');
		
		foreach( $buttons as $key => $val)
		{
			$filtro .= a_button_icon($val["url"],$val["text"], 'icon'.$key, 'ttipDw '.$val["class"],'id="btool-'.$key.'" title="'.$val["title"].'"',$val["color"]);
			
		}	
			$filtro .= form_close();
			
			return $filtro;
		//}
	}
}