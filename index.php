<?php

//Incluimos la libreria principal
include_once './src/Epi.php';
//setear el directorio base de la aplicación, sin esto el framework no podrá
//cargar los módulos necesarios para trabajar
Epi::setPath('base', './src');
 
//cargamos el modulo route
Epi::init('route');
 
//seteamos rutas de acceso y funciones handlers
getRoute()->get('/', 'home');
getRoute()->get('/contacto', 'contactUs');
//ejemplo con paramteros
//aceptamos solo números con esta expresión (\d+)
getRoute()->get('/numeros/(\d+)', 'soloNumeros');
 
//aceptamos solo letras con esta expresión (\w+)
getRoute()->get('/letras/(\w+)', 'soloLetras');
 

getRoute()->run();


 
//funciones handlers
function home() {
       echo 'Estas en la pagina de inicio';
}
 
function contactUs() {
       echo 'Envianos un mail a <a href="mailto:foo@bar.com">foo@bar.com</a>';
}

function soloNumeros($param) {
       echo 'parametro numeros: '.$param;
}
function soloLetras($param) {
       echo 'parametro letras: '.$param;
}

?>