<?php
header("Access-Control-Allow-Origin: *");
//Incluimos la libreria principal

include_once './src/Epi.php';
//Establecer el directorio base de la aplicación, sin esto el framework no podrá
//cargar los módulos necesarios para trabajar
Epi::setPath('base', './src');
//cargamos el modulo route
Epi::init('route','database','api');
//EpiDatabase::employ('mysql','eps','localhost','root','GhotHod4');
EpiDatabase::employ('mysql','eps','localhost','root','');

//seteamos rutas de acceso y funciones handlers
getRoute()->get('/', 'home');

/*
 * Lista de titulaciones
 * GET
 *  [{
 *      "codigo"   : "B02",
 *      "nombre"   : "Ingenieria Informatica",
 *      "acronimo" : "GII",
 *      "cursos"   : [{1,2,3,4}]
 *  }]
 */
getRoute()->get('/titulaciones', 'obtener_titulaciones');

/*
 * Lista de tipos de recursos docentes
 * GET
 *  [{
 *      "codigo"   : "xxx",
 *      "nombre"   : "yyy"
 *  }]
 */
getRoute()->get('/tiposrecursosdocentes', 'obtener_tipos_recursos');

/*
 * Lista de recursos docentes
 * GET
 *  [{
 *      "codigo"   : "xxx",
 *      "nombre"   : "yyy",
 *      "tipo"     : "zzzz"
 *  }]
 */
getRoute()->get('/recursosdocentes', 'obtener_recursos_docentes');

/*
 * Lista de asignaturas para una titulacion y un curso
 * GET
 *  [{
 *      "codigo"   : "xxx",
 *      "nombre"   : "yyy",
 *      "actividades"     : "[{codigo: "nnnnn"}]"
 *  }]
 */
getRoute()->get('/titulaciones/(\w+)/curso/(\d+)/asignaturas', 'obtener_asignaturas_curso');

getRoute()->post('/asignaturas/(\d+)/actividad/(\d+)/recursodocente','asignar_recurso_asignatura');
getRoute()->post('/asignarTipoAulaAsignatura','asignar_tipoAula_asignatura');
getRoute()->delete('/asignaturas/(\d+)/actividad/(\d+)/recursodocente/(\d+)','delete_recurso_asignatura');

getRoute()->get('/asignaturas/(\d+)/actividades','obtener_listado_actividades');


/*
 * Lista de actividades de las asignaturas
 * GET
 *  [{
 *      "codigo"   : "xxx",
 *      "nombre"   : "yyy"
 *  }]
 */
getRoute()->get('/tipoactividades', 'obtener_tipos_actividades');

getRoute()->get('/tiposAulaCentralizadas','obtener_tipos_aulasCentralizadas');
getRoute()->get('/tiposAulaNoCentralizadas','obtener_tipos_aulasNoCentralizadas');

getRoute()->run();


 
//---------------------------funciones handlers-----------------------------------------
function home() {
       echo 'Estas en la pagina de inicio';
}
 
function obtener_titulaciones() {
    $titulaciones = getDatabase()->all('SELECT * FROM planesestudios where TIPO=:tipo',array(':tipo' => "GRADO"));
    return salidaJSON($titulaciones);
}

function obtener_tipos_recursos() {
    return salidaJSON('[{ codigo"   : "xxx",    "nombre"   : "yyy" }]');
}

function obtener_recursos_docentes() {

    $recursos = getDatabase()->all('SELECT * FROM tiposaula');
    return salidaJSON($recursos);

}


function obtener_asignaturas_curso($titulacion,$curso) {

    $asignaturas = getDatabase()->all('select a.* from asignaturas a, asignaturascursos acu where a.CODPLA=:titulacion
                                                         and a.CODASI = acu.CODASI and acu.CURSO ='.$curso
        ,array(':titulacion'=>$titulacion));
    salidaJSON($asignaturas);
}

function obtener_tipos_actividades() {
    return salidaJSON('[{ codigo"   : "xxx",    "nombre"   : "yyy" }]');
}

function asignar_recurso_asignatura($asignatura,$actividad) {
    echo $asignatura . $actividad;


}function asignar_tipoAula_asignatura() {
    $fileData = file_get_contents("php://input");
    $data = json_decode($fileData);
//    INSERT INTO table_name VALUES (value1,value2,value3,...);
    $tipoAula = getDatabase()->execute('INSERT INTO asignaturascursosactiv VALUES (:curso,:codasi,:codact,0.30,:codtipoaula,0.00,0.00,0.00,0,0,0,0.00,:real,0.00)',
        array(':curso'=>"2015-2016",':codasi'=>$data.codasi,':codact'=>$data.codact,':codtipoaula'=>$data.codtipoaula, ':real'=>"REAL"));
    return salidaJSON($fileData);
}

function delete_recurso_asignatura($asignatura,$actividad,$recurso) {
    echo $asignatura . $actividad . $recurso;
}

function obtener_listado_actividades($asignatura) {
   $actividades = getDatabase()->all('select * from asignaturascursosactiv a, asignaturasactividades b where  a.CODASI = :asignatura
                                      and a.CODACT = b.CODACT and a.CURSO=2014',array(':asignatura'=>$asignatura));
    return salidaJSON($actividades);
}

function obtener_tipos_aulasCentralizadas() {
    $tiposAulaCentralizada = getDatabase()->all('select * from tiposaula WHERE TIPOAULA in ("01AULA","03DEPORTE","02INFORM","05LABDOC","04NO")');
    return salidaJSON($tiposAulaCentralizada);
}
function obtener_tipos_aulasNoCentralizadas() {
    $tiposAulaNoCentralizadas = getDatabase()->all('select * from tiposaula WHERE TIPOAULA not in ("01AULA","03DEPORTE","02INFORM","05LABDOC","04NO")');
    return salidaJSON($tiposAulaNoCentralizadas);
}

function salidaJSON ($resultado)
{
    header("Content-Type: application/json");
    echo json_encode(utf8ize($resultado));
}

/*
 * Función que codifica a utf8 si la base de datos no está correcta.
 */
function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}

