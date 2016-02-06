<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Error404 extends Base_Controller { 
   public function index(){
      $this->load_view_unique('error404'); 
   }
}