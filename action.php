<?php
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_redirectssl extends DokuWiki_Action_Plugin {
    function getInfo(){ return conf_loadfile(dirname(__FILE__).'/info.txt'); }
	  function register($contr){
    $contr->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'handle_action',array());
   }
  function handle_action(&$e, $param){
    if($this->isssl()) return; #already ssl, nothing to do.
    $actions=$this->getConf('actions');
    if(is_string($actions)) $actions=explode(',',$actions);
    if(!$actions) return;
    if(!in_array('ALL',$actions) && !in_array($e->data,$actions)) return;
    #mve(['servername'=>$this->servername(),'thisport'=>$this->thisport(),'httpsport'=>$this->httpsport(),'hasssl'=>$this->hasssl()]);

    if($this->getConf('checkssl')&&!$this->hasssl()){
      msg("Redirecting to HTTPS is disabled because this server does not appear to have HTTPS capability. The site admin may turn off checkssl in the redirectssl plugin configuration to turn this check off and force redirect to HTTPS anyway.");
      return;
    }
    if(!($servername=$this->servername())){
      msg("Redirecting to HTTPS is disabled because I could not determine the servername. The site admin may manually set the servername in the redirectssl plugin configuration.");
      return;
    }
    if(!($port=$this->getConf('httpsport'))){
      msg("Redirecting to HTTPS is disabled because I could not determine the HTTPS port number. The site admin may manually set the port number in the redirectssl plugin configuration.");
      return;
    }
 
    $url="https://$servername".($port==443?'':":$port").'/'.preg_replace('#^/#','',$_SERVER['REQUEST_URI']);
    $url=str_replace("\r",rawurlencode("\r"),$url);
    $url=str_replace("\n",rawurlencode("\n"),$url);
    header("Location: $url");
    exit();
  }

  function isssl(){
    if (isset($_SERVER['HTTPS']))
    return !preg_match('/^(|off|false|disabled)$/i', $_SERVER['HTTPS']);
    elseif(isset($_SERVER['SCRIPT_URI'])&&strpos($_SERVER['SCRIPT_URI'],'https://')===0) return true;
    return isset($_SERVER['SERVER_PORT'])&&preg_match('#443$#',$_SERVER['SERVER_PORT']);  
  }
  function servername(){
    if($ret=$this->getConf('servername')) return $ret;
    $ret=$_SERVER['HTTP_HOST']?: ($_SERVER['SERVER_NAME']?: ($_SERVER['COMPUTERNAME']? : NULL));
    if($ret) return preg_replace('#:\d+$#','',$ret);
  }
  function thisport(){
    if($ret=$_SERVER['SERVER_PORT']) return $ret;
    if($ret=$_SERVER['HTTP_HOST']) return preg_replace('#^.*:(\d+)$#','$1',$ret);
  }
  function httpsport(){
    if($ret=$this->getConf('httpsport')) return $ret;
    if(!$this->thisport()||$this->thisport()==80||$this->thisport()==443) return 443; #only return 443 if the current port is not available or it is the standard 80 or this is already 443.
  }
  function hasssl(){
    static $ret; if(isset($ret)) return $ret;	
    #I think the server_software string used to list the modules, but doesn't seem to be the case anymore, so the following if() is now useless.
    if(isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'],'mod_ssl')!==FALSE)
    return $ret=true;
    
    try{ #need to use try-catch in case this is not an apache server.
      if(in_array('mod_ssl', apache_get_modules())) return $ret=true;
    }catch(Exception $e){}
    
    if($port=$this->httpsport()){
      $socket = @socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
      if ($socket >= 0 && @socket_connect($socket,$this->servername()?:'127.0.0.1',$port)>=0){
        @socket_close($socket);
        return $ret=true;
      }
      @socket_close($socket);
    }  
    return $ret=false;
  }
  

}
