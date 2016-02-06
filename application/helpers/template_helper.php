<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    if (!function_exists('input_text_tpl')) {
        function input_text_tpl(){
            
        }
    }
    if(!function_exists('return_avatar')){
        function return_avatar($folder, $file='', $center = true, $width = '35' , $height = '35' , $cropper = false){
            $folder = trim($folder ,'/').'/';
            if($file==''){
                $file = 'sin_foto.png';
            }
            $cropper = ($cropper) ? 'image-edit' : '';
            //$tooltip = ($cropper) ? ' title = "Click para Editar" ' : '';
            $img = '<img '.$tooltip.' class = "img-polaroid '. $cropper .'" style="cursor:pointer;max-width:'.$width.'px;min-height:'.$height.'px;"  src="'.base_url().'assets/avatar/'.$folder.$file.'"  >';

            if($center){
                $img = '<center>'.$img.'</center>';
            }

           
            return $img;        
        }
    }
    if(!function_exists('add_on_tpl')){
        function add_on_tpl($input, $eti){
            $add_on = "<span class='field input-prepend input-append'>
                                ".$input."
                               <span class='add-on'>".$eti."</span>     
                        </span>";
            return $add_on;
        }
    }
    if(!function_exists('input_error_tpl')){
        function input_bootstrap_tpl($data, $type = false){
            $class = ($type) ? "has-".$type : ''; 
            $input = "<div class='form-group ".$class."'>".
                                form_input($data)." 
                        </div>";
            return $input;
        }
    }   
    if(!function_exists('tabbed_tpl')){
        function tabbed_tpl($config, $uri_string, $segment = 1, $content = ""){
            $link         = "";
            $tabs         = "";
            $tabbed       = "";
            $tabs_li      = "";
            $tabs_content = "";
            $total_tabs   = count($config['names']);

            for ($i=0; $i < $total_tabs; $i++) { 
                $activate = ($i==($segment-1)) ? 'ui-tabs-active ui-state-active' : "";
                $selected = ($i==($segment-1)) ? 'true' : "";
                $display  = ($i==($segment-1)) ? 'display: block' : "";
                if(is_array($content)){
                    $data = (array_key_exists($i, $content) ) ? $content[$i] : '';
                }else{
                    $data     = ($i==($segment-1)) ? $content : "";
                }
                
                $uri_string = ($uri_string=="") ? "" : trim($uri_string,'/').'/';

                $link    = ($config['links'][$i]=="") ? '"'.trim($uri_string,'/').'"'  : '"'.$uri_string.$config['links'][$i].'"';
                
                if($config['action'][$i]==""){
                    $onclick  = "";
                }else{
                    $action = $config['action'][$i];
                    if(is_array($action)){
                        foreach ($action as $function => $params) {
                            if(is_array($params)){
                                $params = implode(',', $params);
                            }
                            $onclick  = "onclick='$function($link, $params);'";
                        }
                    }else{
                        $onclick  = "onclick='$action($link, $i);'";
                    }
                }

                $attr     = array_2_string_format($config['attr'][$i]);
                $tabs_li .= "<li   class='ui-state-default ui-corner-top $activate' role='tab' tabindex='$i' aria-controls='a-$i' aria-labelledby='ui-id-$i' aria-selected='$selected'>
                                <a $attr href='#a-$i' $onclick class='ui-tabs-anchor' role='presentation' tabindex='$i' id='ui-id-$i'>
                                    ".$config['names'][$i]."
                                </a>
                            </li>";
                if(array_key_exists('style_content',$config)){
                    if(array_key_exists($i,$config['style_content'])){
                        $overflow_x = $config['style_content'][$i];
                    }else{
                        $overflow_x = "overflow-x:auto;";
                    }
                }else{
                    $overflow_x = "overflow-x:auto;";
                }
                $tabs_content .= "<div id='a-$i' aria-labelledby='ui-id-$i' class='ui-tabs-panel ui-widget-content ui-corner-bottom' role='tabpanel' aria-expanded='$selected' aria-hidden='false' style='$overflow_x $display'>
                                    $data
                                    </div>";
            }

            $tabbed .= "<div class='tabbedwidget tab-primary ui-tabs ui-widget ui-widget-content ui-corner-all' style='overflow:visible;'>";
            $tabbed .= "<ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all' role='tablist'>";
            $tabbed .= $tabs_li;
            $tabbed .= "</ul>";
            $tabbed .= $tabs_content;
            $tabbed .= "</div>";
            return $tabbed;
        }
    }
    if(!function_exists('alertas_tpl')){
        function alertas_tpl($type = '', $mensaje = '' ,$close = false, $width = false){
            $alert = "";
            $width = ($width) ? 'style = width:'.$width : '';
            $button_close = "";
            if($type == ""){
                $type = "alert";
            }else{
                $type = "alert-$type";
            }
            if($close){
                $button_close = "button data-dismiss='alert' class='close' type='button'>×</button>";
            }
            
            $alert ="<div class='alert $type' ".$width." style='text-align:justify;''> $mensaje </div>";

            return $alert;
        }
    }
    if(!function_exists('text_format_tpl')){
        function text_format_tpl($string, $format = "f"){
            if($string==''){
                return $string;
            }
            if($format=="f"){
                return ucfirst(strtolower($string));
            }else{
                return ucwords(strtolower($string));
            }
        }
    }
    if(!function_exists('tool_tips_tpl')){
        function tool_tips_tpl($value, $tool_tip = '', $pocision = 'right', $attr = array()){
            $attr = array_2_string_format($attr);
            /*$tool_tip ="<ul class='tooltips'>
                            <li><a $attr data-placement='$pocision' data-rel='tooltip'  data-original-title='$tool_tip' >$value</a></li>
                        </ul>";*/
            $tool_tip ="<span><a $attr title='$tool_tip'>$value</a></span>";
            return $tool_tip ;
        }
    }
    if(!function_exists('dropdown_tpl')){
        // Crea una lista <select>
        function dropdown_tpl($params=array()){ 
            if(!empty($params)){
                $data       = (isset($params['data']))?$params['data']:false;
                $selected   = (isset($params['selected']))?$params['selected']:'';
                $value      = (isset($params['value']))?$params['value']:false;
                $text       = (isset($params['text']))?$params['text']:false;
                $name       = (isset($params['name']))?$params['name']:false;
                $class      = (isset($params['class']))?$params['class']:'';
                //$event        = (isset($params['event']))?$params['event']:'';
                $event      = (array_key_exists('event',$params))?data_event_tpl($params['event']):false;
                $event      = ($event) ? $event : ''; 
                $disabled   = (isset($params['disabled']))?$params['disabled']:'';

                $leyenda    = (array_key_exists('leyenda' ,$params))?$params['leyenda']: '-----';

            }
            $name         = ($name=="")?"selected": $name;
            $count        = 0;
            if($data && $name && $value && $text){
                foreach ($data as $option => $item) {
                    $option_value = "";
                    if($count==0){
                        $options[0]= $leyenda;
                    }
                    if(is_array($text)){
                        foreach ($text as $string) {
                            $option_value .= $item[$string].'-';
                        }
                        $options[$item[$value]] = trim($option_value, '-');
                    }else{
                        $options[$item[$value]]= $item[$text];
                    }                   
                    $count++;
                }

                $selected = "<span class='formwrapper'>".form_dropdown($name, $options, $selected, " class='chzn-select $class' $event $disabled data-campo='$name'")."</span>";
                return $selected;
            }
            if(!$data){
                $leyenda = array($leyenda);
                $selected = "<span class='formwrapper'>".form_dropdown($name,$leyenda, $selected," class='chzn-select $class' ")."</span>";
                return $selected;
            }
            return false;
        }
    }
    if(!function_exists('multi_dropdown_tpl')){
        // Crea una lista <select>
        function multi_dropdown_tpl($params=array()){           
            if(!empty($params)){
                $data       = (isset($params['data']))?$params['data']:false;
                
                $selected   = (isset($params['selected']))?$params['selected']:'';
                $value      = (isset($params['value']))?$params['value']:false;
                $text       = (isset($params['text']))?$params['text']:false;
                $name       = (isset($params['name']))?$params['name']:false;
                $class      = (isset($params['class']))?$params['class']:'';
                //$event        = (isset($params['event']))?$params['event']:'';
                $event      = (array_key_exists('event',$params))?data_event_tpl($params['event']):false;
                $event      = ($event) ? $event : ''; 
                $disabled   = (isset($params['disabled']))?$params['disabled']:'';
                $leyenda    = (isset($params['leyenda']))?$params['leyenda']:'-----';
            }

            $name         = ($name=="")?"multi_selected": $name;
            $count        = 0;
            if($data && $name && $value && $text){
                
                $s = array();
                foreach ($data as $option => $item) {
                    $option_value = "";
                    if(is_array($value)){
                        $v = array();

                        foreach ($value as $i) {
                            $v[] = $item[$i];
                        }
                        $v = implode('|', $v);
                        $s[] = $v;
                        
                    }else{
                        $v = $item[$value];
                    }
                     
                    if(is_array($text)){
                        foreach ($text as $string) {
                            $option_value .= $item[$string].'-';
                        }

                        $options[$v] = trim($option_value, '-');
                    }else{
                        $options[$v]= $item[$text];
                    }                   
                    $count++;
                }

                if($selected=='all'){
                    $selected = array();
                    $selected = $s;
                }
                //print_debug($options);
                $selected = "<span class='formwrapper '>".form_multiselect($name, $options, $selected, " class=' clean chosen-multiselect input-xlarge chzn-select $class' $event $disabled data-campo='$name'")."</span>";
                return $selected;
            }
            $selected = "<span class='formwrapper '>".form_multiselect($name, array(), $selected, " class=' clean chosen-multiselect input-xlarge chzn-select $class' $event $disabled data-campo='$name'")."</span>";
            return $selected;
        }
    }
    if(!function_exists('multiple_select_tpl')){
        function multiple_select_tpl($params = array()){
            if(!empty($params)){
                $data          = (isset($params['data']))?$params['data']:false;
                $data_seleted  = (isset($params['data_seleted']))? $params['data_seleted'] : array();
                $selected      = (isset($params['selected']))?$params['selected']:'';
                $value         = (isset($params['value']))?$params['value']:false;
                $text          = (isset($params['text']))?$params['text']:false;
                $name          = (isset($params['name']))?$params['name']:false;
                $class         = (isset($params['class']))?$params['class']:'';
                $event         = (isset($params['event']))?$params['event']:'';
                $prev          = (isset($params['prev']))?$params['prev']:'';
                $next          = (isset($params['next']))?$params['next']:'';
                $disabled      = (isset($params['disabled']))?$params['disabled']:'';
            }

            $name    = ($name=="") ? "selected" : $name;
            $count   = 0;
            $options_selected = array();

            if($data && $name && $value && $text){
                if(!empty($data_seleted)){
                    foreach($data_seleted as $option => $item){
                        $option_selected = "";
                        if(is_array($text)){
                            foreach ($text as $string) {
                                $option_selected .= $item[$string].'-';
                            }
                            $options_selected[$item[$value]] = trim($option_selected, '-');
                        }else{
                            $options_selected[$item[$value]]= $item[$text];
                        }       
                    }
                }
                $options = array();
                foreach ($data as $option => $item){
                    $option_value = "";
                    if(is_array($text)){
                        if(!array_key_exists($item[$value], $options_selected)){
                            foreach ($text as $string) {
                                $option_value .= $item[$string].'-';
                            }
                            $options[$item[$value]] = trim($option_value, '-');
                        }
                    }else{
                        if(!array_key_exists($item[$value], $options_selected)){
                            $options[$item[$value]]= $item[$text];
                        }
                    }                   
                    $count++;
                }
                //$multiple =  form_multiselect($name2, $options_selected, $selected,  "multiple='multiple' class='multiple_selected $class'  size='10'");

                $selected = form_multiselect($name, $options, $selected,"multiple='multiple' class='uniformselect' size='10'");

                               
                return $selected;
            }
            return form_multiselect('', array(), '',"multiple='multiple' class='uniformselect' size='10'");
        }
    }
    if(!function_exists('dropMultiselect_tpl')){
        // Crea una lista <multiselect> 
        function dropMultiselect_tpl($params=array()){          
            if(!empty($params)){
                $data          = (isset($params['data']))?$params['data']:false;
                $data_seleted  = (isset($params['data_seleted']))? $params['data_seleted'] : array();
                $selected      = (isset($params['selected']))?$params['selected']:'';
                $value         = (isset($params['value']))?$params['value']:false;
                $text          = (isset($params['text']))?$params['text']:false;
                $name          = (isset($params['name']))?$params['name']:false;
                $name2         = (isset($params['name2']))?$params['name2']:'list';
                $class         = (isset($params['class']))?$params['class']:'';
                $event         = (isset($params['event']))?$params['event']:'';
                $prev          = (isset($params['prev']))?$params['prev']:'';
                $next          = (isset($params['next']))?$params['next']:'';
                $disabled      = (isset($params['disabled']))?$params['disabled']:'';
            }

            $name    = ($name=="") ? "selected" : $name;
            $count   = 0;
            $options_selected = array();

            if($data && $name && $value && $text){
                if(!empty($data_seleted)){
                    foreach($data_seleted as $option => $item){
                        $option_selected = "";
                        if(is_array($text)){
                            foreach ($text as $string) {
                                $option_selected .= $item[$string].'-';
                            }
                            $options_selected[$item[$value]] = trim($option_selected, '-');
                        }else{
                            $options_selected[$item[$value]]= $item[$text];
                        }       
                    }
                }
                $options = array();
                foreach ($data as $option => $item){
                    $option_value = "";
                    if(is_array($text)){
                        if(!array_key_exists($item[$value], $options_selected)){
                            foreach ($text as $string) {
                                $option_value .= $item[$string].'-';
                            }
                            $options[$item[$value]] = trim($option_value, '-');
                        }
                    }else{
                        if(!array_key_exists($item[$value], $options_selected)){
                            $options[$item[$value]]= $item[$text];
                        }
                    }                   
                    $count++;
                }
                $multiple =  form_multiselect($name2, $options_selected, $selected,  "multiple='multiple' class='multiple_selected $class'  size='10'");

                $selected = "<span id='dualselected' class='dualselect'>"
                            .form_multiselect($name, $options, $selected,"multiple='multiple' class='uniformselect' size='10'")

                               ."<span class='ds_arrow'>
                                    <button onclick='$prev' class='btn ds_prev'>
                                        <i class='iconfa-chevron-left'>
                                        </i>
                                    </button>
                                    <br>
                                    <button onclick='$next' class='btn ds_next'>
                                        <i class='iconfa-chevron-right'>
                                        </i>
                                    </button>
                                </span>"
                                .$multiple
                            ."</span>";
                return $selected;
            }
            return false;
        }
    }
    if(!function_exists('button_tpl')){
        function button_tpl($params=array()){
            $button = "";
            $main   = "";
            $btn    = "";
            if(!empty($params)){
                $text       = (array_key_exists('text', $params))?$params['text']:false;
                $type       = (array_key_exists('type',$params))?$params['type']: array();
                $id         = (array_key_exists('id',$params))?$params['id']: array();
                $iconsweets = (array_key_exists('iconsweets',$params))?$params['iconsweets']:'';
                $event      = (array_key_exists('event',$params))?$params['event']:array();
                $href       = (array_key_exists('href',$params))?$params['href']:false;
            }else{
                return false;
            }

            if(is_array($text)){
                $i= 0;
                foreach ($text as $key => $value) {
                    $label = (array_key_exists($i, $text) ) ? $text[$i] : '';
                    $icon  = (array_key_exists($i, $iconsweets) )  ? $iconsweets[$i] : 'iconsweets-link'; 
                    $link  = (array_key_exists($i, $href) )  ? ($href[$i]=='') ? '' :  "href='".$href[$i]."'" : ''; 
                    $ident = (array_key_exists($i, $id) ) ? ($id[$i]=='') ? '' : "id='".$id[$i]."'" : ''; 
                    
                    
                    
                    if(array_key_exists($i, $type)){
                        $evt   = (array_key_exists($i, $event) )? explode('=',data_event_tpl($event[$i]) ) :'';
                        $file  = trim(str_replace(' ', '', $label));
                        $btn   = "<a $ident onclick ='document.getElementById(".'"'.$file.'"'.").click();' class='btn btn-primary'> 
                                    <i class='$icon'></i> &nbsp; 
                                    $label
                                </a>";

                        $data = array(  
                                        'id'    => $file,
                                        'type'  => 'file',
                                        'name'  => "userfile",
                                        'style' => ''
                                    );
                        if(is_array($evt)){
                            $data[$evt[0]] = str_replace("'", "", $evt[1] );
                        }
                        $btn.= form_input($data);
                    }else{
                        $evt   = (array_key_exists($i, $event) )? data_event_tpl($event[$i] ) :'';
                        $btn = "<a $link $evt $ident class='btn btn-primary'> 
                                    <i class='$icon'></i> &nbsp; 
                                    $label
                                </a>";
                    }
                    
                    $main .= '<li>'.$btn.'</li>';
                    $i++;
                }

                $button .= '<ul class="list-nostyle list-inline">'.$main.'</ul>';
            }else{
                $label = $text;
                $icon  = ($iconsweets) ? $iconsweets: 'iconsweets-link'; 
                $event = (array_key_exists('event',$params))?data_event_tpl($params['event']):'';
                $link  = ($href) ? "href='".$href."'" : ''; 
                $button = "<ul class='list-nostyle list-inline'><li><a $link $event class='btn btn-primary'> <i class='$icon'></i> &nbsp; $label</a> </li></ul>";
            }

            return $button;
        }
    }
    if(!function_exists('data_event_tpl')){
        function data_event_tpl($data = ''){
            $var = array();
            if(is_array($data)){
                if(!empty($data)){
                    $event       = (array_key_exists('event', $data)) ? $data['event'] : false;
                    $function    = (array_key_exists('function', $data)) ? $data['function'] : false;
                    $params      = (array_key_exists('params', $data)) ? $data['params'] : false;
                    $params_type = (array_key_exists('params_type', $data)) ? $data['params_type'] : false;
                    $count = 0;
                    if($event){
                        if($function){
                            if(is_array($params)){
                                foreach ($params as $key => $value) {
                                    if($params_type){
                                        $vars[] = ($params_type[$count]) ? '"'.$value.'"' : $value;
                                        $count++;
                                    }else{
                                        $vars[] = '"'.$value.'"';
                                    }
                                }
                                $params = implode(',', $vars);
                                $event = $event."='".$function."(".$params.");"."'";
                            }else{
                                $event = $event."='".$function."();'";
                            }
                            return $event;
                        }
                    }
                    return false;
                }
                return false;
            }
            return false; 
        }
    }
    if(!function_exists('plantilla_table_tpl')){
        function set_table_tpl(){
           return  array (
                            'table_open'          => '<table class="table table-bordered responsive dataTable" >',

                            'heading_row_start'   => '<tr>',
                            'heading_row_end'     => '</tr>',
                            'heading_cell_start'  => '<th>',
                            'heading_cell_end'    => '</th>',

                            'row_start'           => '<tr>',
                            'row_end'             => '</tr>',
                            'cell_start'          => '<td style="max-width:80px;text-align:left;word-wrap:break-word;"><p style="white-space: pre">',
                            'cell_end'            => '</p></td>',

                            'row_alt_start'       => '<tr>',
                            'row_alt_end'         => '</tr>',
                            'cell_alt_start'      => '<td style="max-width:80px;text-align:left;word-wrap:break-word;background: #eee;"><p style="white-space: pre">',
                            'cell_alt_end'        => '</p></td>',

                            'table_close'         => '</table>'
                      );
                
        }
    }
    if(!function_exists('widgetbox_tpl')){
        function widgetbox_tpl($name, $content){
            
            $widgetbox = "<div class=''>
                            <h1 class=''>Menu:
                                <span class='' id='html_periodo_1'>$name</span>
                            </h1>
                            <div class=''>
                            $content
                            </div>
                        </div>
                            ";

            return $widgetbox;
        }
    }
    if(!function_exists('toggle_modal_tpl')){
        function toggle_modal_tpl($identi = '', $config = array(), $modal= true, $static= false){
            $header = (array_key_exists('header',$config)) ? $config['header'] : array();
            $body   = (array_key_exists('body',$config)) ? $config['body'] : array();
            $footer = (array_key_exists('footer',$config)) ? $config['footer'] : array();

            if($static){
                $backdrop = 'data-backdrop="static"';
                $close = '';
            }else{
                $backdrop = '';
                $close = '<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>';
            }

            $id_header = (array_key_exists('id', $header))? 'id="'.$header['id'].'"' : '';
            $header    = (array_key_exists('html', $header))? $header['html'] : '';
            
            $id_body   = (array_key_exists('id', $body))? 'id="'.$body['id'].'"' : ''; 
            $body      = (array_key_exists('html', $body))? $body['html'] : '';

            $id_footer = (array_key_exists('id', $footer))? 'id="'.$footer['id'].'"' : '';  
            $footer    = (array_key_exists('html', $footer))? $footer['html'] : '';
            
            if($modal){
                $content = '<div aria-hidden="false" '.$backdrop.' aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade in" id="'.$identi.'">';
            }else{
                $content ='<div id="'.$identi.'">';
                $close = '';
            }
            $modal =
                   $content.'
                        
                        <div class="modal-header" '.$id_header.'>
                            '.$close.'
                            <h3 id="myModalLabel">'.$header.'</h3>
                        </div>

                        <div  class="modal-body"  '.$id_body.'>
                            '.$body.'
                        </div>

                        <div class="modal-footer" '.$id_footer.'>
                            '.$footer.'
                        </div>
                    </div>';
                    //print_debug($modal);
            return $modal;  
        }   
    }

?>