<?php

define("BASEDIR",realpath(dirname(dirname(__FILE__))));

// Request 

function cms_route(){
  $path=$_SERVER["PATH_INFO"];
  return $path;
}

function cms_params(){
  return $_REQUEST;
}


// Files

function cms_path($path){
  return BASEDIR."/$path";
}

function cms_file($path){
  return file_get_contents(cms_path($path));
}


// JSON files

function cms_fromjson($data){
  $data=json_decode($data,true);
  return $data;
}

function cms_config($name){
  return cms_fromjson(cms_file("config/$name.json"));
}

function cms_route_config($route){
  $method = strtolower($_SERVER["REQUEST_METHOD"]);
  $routes = cms_config("index")[$method];
  $routeConfig = $routes[$route]?:$routes["/"];
  return $routeConfig;
}

function cms_template($file,$data=[]){
  $template = cms_file($file);
  $matches=[];
  $vars = preg_match_all("/\{\{([^\{]+)\}\}/",$template,$matches);
  $originals= $matches[0];
  $finals = $matches[1];
  foreach($originals as $pos=>$search){
    $varname = trim($finals[$pos]);
    if ($data[$varname]!==null){
      $content = $data[$varname];
      if (is_array($content)){
        $result = "";
        foreach($content as $idx => $item ){
          $config = cms_config("components/".$item["component"]);
          $config = array_merge($config,$item);
          $result .= cms_template($config["template"],$config); 
        }
        $content = $result;
      }
      $template = str_replace($search,$content,$template);
    }
  }
  return $template;
}

function cms_view($route,$params=[]){
  $routeConfig = cms_route_config($route);
  $config = cms_config("components/".$routeConfig["component"]);
  $config = array_merge($config,$routeConfig);
  $template = cms_template($config["template"],$config);
  return $template;
}
