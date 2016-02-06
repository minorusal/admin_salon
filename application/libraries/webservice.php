<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH."third_party/ws-nusoap/nusoap.php");

class webservice extends nusoap_base{	
	public $ws_script, $ws;
	public function __construct(){
		parent::__construct();
		//Setup del WSDL
		// $this->ws_script = ($this->ws_script)?$this->ws_script:'';
		$this->ws = new soap_server();
		$this->ws->configureWSDL('ws-server',$this->ws_script);
		$this->ws->wsdl->schemaTargetNamespace=$this->ws_script; 
	}			
}