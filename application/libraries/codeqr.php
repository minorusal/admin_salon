<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Descripción:  Crea imagen de código QR en formato PGN
* @author:      Oscar Maldonado - O3M
* Creación:     2015-05-26
*/
// Clases requeridas
require_once (APPPATH.'third_party/qrcode/qrlib.php'); 

class codeqr extends QRcode{

    protected $archivo; #Archivo
    protected $errorCorrectionLevel, $matrixPointSize, $margin; #Formato
    
    function __construct(){
        $vars = new config_vars();
        $vars->load_vars('assets/cfg/ticket.cfg');
        $this->archivo              = $vars->cfg['path_tmp'].'qrcode.png';
        $this->errorCorrectionLevel = $vars->var['ticket']['qrcode_error_correction_level']; #'L','M','Q','H'
        $this->matrixPointSize      = $vars->var['ticket']['qrcode_point_size']; # 1-10
        $this->margin               = $vars->var['ticket']['qrcode_margin'];
    }

    public function create($texto=''){
        if($texto){
            $this->png($texto, $this->archivo, $this->errorCorrectionLevel, $this->matrixPointSize, $this->margin);           
            return $this->archivo;
        }else{
            return false;
        }
    }
}
?>