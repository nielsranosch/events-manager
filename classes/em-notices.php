<?php
    /**
     * 
     * @author marcus
     *
     */
    class EM_Notices implements Iterator {
        var $notices = array('errors'=>array(), 'infos'=>array(), 'alerts'=>array(), 'confirms'=>array());
        
        function __construct(){
        	session_start();
        	//Grab from session
        	if( !empty($_SESSION['events']['notices']) && is_serialized($_SESSION['events']['notices']) ){
        		$this->notices = unserialize($_SESSION['events']['notices']);
        	}
        	//Flush notices that weren't made to stay cross-requests, we can do this if initialized immediately.
        	foreach($this->notices as $notice_type => $notices){
        		foreach ($notices as $key => $notice){
        			if( empty($notice['static']) ){
        				unset($this->notices[$notice_type][$key]);
        			}else{
        				unset($this->notices[$notice_type][$key]['static']); //so it gets removed next request
        			}
        		}
        	}
            add_action('shutdown', array(&$this,'destruct'));
            add_filter('wp_redirect', array(&$this,'destruct'), 1,1);
        }
        
        function destruct($redirect = false){
        	$_SESSION['events']['notices'] = serialize($this->notices);
        	return $redirect;
        }
        
        function __toString(){
            $string = false;
            if(count($this->notices['errors']) > 0){
                $string .= "<div class='em-warning em-warning-errors error'>{$this->get_errors()}</div>";
            }
            if(count($this->notices['alerts']) > 0){
                $string .= "<div class='em-warning em-warning-alerts updated'>{$this->get_alerts()}</div>";
            }
            if(count($this->notices['infos']) > 0){
                $string .= "<div class='em-warning em-warning-infos updated'>{$this->get_infos()}</div>";
            }
            if(count($this->notices['confirms']) > 0){
                $string .= "<div class='em-warning em-warning-confirms updated'>{$this->get_confirms()}</div>";
            }
            return ($string !== false) ? "<div class='statusnotice'>".$string."</div>" : '';
        }
        
        /* General */
        function add($string, $type, $static = false){
        	if( is_array($string) ){
        		$result = true;
        		foreach($string as $string_item){
        			if( $this->add($string_item, $type, $static) === false ){ $result = false; }
        		}
        		return $result;
        	}
            if($string != ''){
                if( isset($this->notices[$type]) ){
                	foreach( $this->notices[$type] as $notice_key => $notice ){
                		if($string == $notice['string']){
                			return $notice_key;
                		}
                	}
                    $i = key($this->notices[$type])+1;
                    $this->notices[$type][$i]['string'] = $string;
                    if( $static ){
                    	$this->notices[$type][$i]['static'] = true;
                    }
                    return $i;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        function remove($key, $type){
            if( isset($this->notices[$type]) ){
                unset($this->notices[$type][$key]);
                return true;
            }else{
                return false;
            }
        }
        function get($type){
            if( isset($this->notices[$type]) ){
                foreach ($this->notices[$type] as $key => $error){
                    $class = substr($type, 0, (strlen($type)-1));
                    $string .= "<p>{$error['string']}</p>";
                    if($error['static'] !== true){
                        $this->remove($key, $type);
                    }
                }
                return $string;
            }else{
                return false;
            }
        }
        
        /* Errors */
        function add_error($string, $static=false){
            return $this->add($string, 'errors', $static);
        }
        function remove_error($key){
            return $this->remove($key, 'errors');
        }
        function get_errors(){
            return $this->get('errors');
        }

        /* Alerts */
        function add_alert($string, $static=false){
            return $this->add($string, 'alerts', $static);
        }
        function remove_alert($key){
            return $this->remove($key, 'alerts');
        }
        function get_alerts(){
            return $this->get('alerts');
        }
        
        /* Info */
        function add_info($string, $static=false){
            return $this->add($string, 'infos', $static);
        }
        function remove_info($key){
            return $this->remove($key, 'infos');
        }
        function get_infos(){
            return $this->get('infos');
        }
        
        /* Confirms */
        function add_confirm($string, $static=false){
        	return $this->add($string, 'confirms', $static);
        }
        function remove_confirm($key){
            return $this->remove($key, 'confirms');
        }
        function get_confirms(){
            return $this->get('confirms');
        }    

		//Iterator Implementation
	    function rewind(){
	        reset($this->bookings);
	    }  
	    function current(){
	        $var = current($this->bookings);
	        return $var;
	    }  
	    function key(){
	        $var = key($this->bookings);
	        return $var;
	    }  
	    function next(){
	        $var = next($this->bookings);
	        return $var;
	    }  
	    function valid(){
	        $key = key($this->bookings);
	        $var = ($key !== NULL && $key !== FALSE);
	        return $var;
	    }        
        
    }
    global $EM_Notices;
    $EM_Notices = new EM_Notices();
?>