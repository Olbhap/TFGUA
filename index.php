<?php
header("Access-Control-Allow-Origin: *");
//Incluimos la libreria principal

include_once './src/Epi.php';
//Establecer el directorio base de la aplicación, sin esto el framework no podrá
//cargar los módulos necesarios para trabajar
Epi::setPath('base', './src');
//cargamos el modulo route
Epi::init('route','database','api');
EpiDatabase::employ('mysql','eps','localhost','root','GhotHod4');
//EpiDatabase::employ('mysql','eps','localhost','root','');

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
 * Lista de recursos docentes
 * GET
 *   [{
 *  "tiporecurso": "1",
 *  "descrip"   : "Matlab",
 *  "codcatrecurso": "1",
 *  "catdescrip": “Software”
 *   }]
 */
getRoute()->get('/tiposrecursosdocentes', 'obtener_tipos_recursos');

/* Lista de recursos docentes
*  GET
*   [{
*   "codtipoaula": "2",
*   "tipoaula"   : "01Aula",
*   "descrip": "Aula de Teoria",
*   "codeps": “TEO”
*   }]
*/

getRoute()->get('/recursosdocentes', 'obtener_recursos_docentes');

/*
 * Lista de asignaturas para una titulacion y un curso
 * GET
 *  [{
 *      "codigo"   : "xxx",
 *      "nombre"   : "yyy", *
 *  }]
 */
getRoute()->get('/titulaciones/(\w+)/curso/(\d+)/asignaturas', 'obtener_asignaturas_curso');

/*
 * Inserta un recurso a una asignatura/actividad
POST
Params:
{
   “curso”       : “xxx”
   “codasi”      : ”yyy”
   “codact”      : “zzz”
   “codtipoaula” : “x”
}
 */
getRoute()->post('/asignarRecursoDocente','asignar_recurso_asignatura');

/*
Inserta un tipoAula a una asignatura/actividad
POST
Params:
{
    “curso”       : “xxx”
   “codasi”      : ”yyy”
   “codact”      : “zzz”
   “codtipoaula” : “x”
}
*/
getRoute()->post('/asignarTipoAulaAsignatura','asignar_tipoAula_asignatura');

/*
 Lista de actividades para una asignatura
GET
[{
  "codact"    : "xxx",
  "ciclo"     : "yy",
  "tipoactiv" : "yy",
  "actividad" : "yyyy",
  "ratioalu"  : "yyyy",
  “ratiominimo": "yy",
  "coeficiente": "yy",
  "porsesiones": "yy",
}]
 */
getRoute()->get('/asignaturas/(\d+)/actividades','obtener_listado_actividades');
/*
 Lista de recursos para una asignatura y una actividad
GET
[{
  "tiporecurso"   : "xxx",
  "descrip"       : "yy",
  "codcatrecurso" : "yy",
}]

 */
getRoute()->get('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/recursos','obtener_listado_recursoss');
/*
 Lista de TiposAula para una asignatura y una actividad
GET
[{
  "codtipoaula"  : "xxx",
  "tipoaula"     : "yy",
  "descrip"      : "zz",
  "codeps"       : "xx",
}]
 */
getRoute()->get('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/aula','obtener_listado_tiposAula');
getRoute()->get('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/aulasNoCentralizadas','obtener_listado_tiposAula_noCentralizadas');

/*
Elimina un TipoAula para una asignatura y actividad
DELETE
Return:
-1    : Error
count : Integer con la cantidad de filas afectadas

 */

getRoute()->delete('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/tipoAula/(\d+)','remove_tiposAula_asignatura_actividad');
/*
Elimina un TipoAula para todas las actividades de una asignatura.
DELETE
Return:
-1    : Error
count : Integer con la cantidad de filas afectadas

 */
getRoute()->delete('/asignaturas/(\d+)/curso/(.+)/tipoAula/','remove_tiposAula_asignatura');

/*
Elimina un recurso para una asignatura y actividad dados
DELETE
Return:
-1    : Error
count : Integer con la cantidad de filas afectadas
 */
getRoute()->delete('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/recursos/(\d+)','remove_recurso_asignatura_actividad');
getRoute()->delete('/asignaturas/(\d+)/actividad/(\d+)/curso/(.+)/recursos','remove_recurso_asignatura_actividad_all');

/*
Elimina los recursos de todas las actividades de una asignatura.
DELETE
Return:
-1    : Error
count : Integer con la cantidad de filas afectadas
 */
getRoute()->delete('/asignaturas/(\d+)/curso/(.+)/recursos','remove_recurso_asignatura_all');


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
    $recursos = getDatabase()->all('select t.*, c.DESCRIP AS CATDESCRIP from tiposrecurso t, categoriasrecurso c
                              WHERE t.CODCATRECURSO = c.CODCAT');
    return salidaJSON($recursos);
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

function asignar_recurso_asignatura() {
    $fileData = file_get_contents("php://input");
    $data = json_decode($fileData);
    $recurso= getDatabase()->execute('INSERT INTO asignaturascursosactivrecursos (CURSO, CODASI, CODACT, TIPORECURSO)
              VALUES (:curso, :codasi, :codact, :codtipoaula)',
        array(':curso'=>$data->curso,':codasi'=>$data->codasi,':codact'=>$data->codact,':codtipoaula'=>$data->TIPORECURSO));
    echo($recurso);


}function asignar_tipoAula_asignatura() {
    $fileData = file_get_contents("php://input");
    $data = json_decode($fileData);
    $tipoAula = getDatabase()->execute('UPDATE asignaturascursosactiv set CODTIPOAULA = :codtipoaula
                                        WHERE CODASI = :codasi AND CODACT = :codact AND CURSO = :curso',
        array(':curso'=>$data->curso,':codasi'=>$data->codasi,':codact'=>$data->codact,':codtipoaula'=>$data->CODTIPOAULA));
    echo($tipoAula);
}

function remove_recurso_asignatura_actividad($asignatura, $actividad, $curso, $recurso) {
    $resultado = getDatabase()->execute('DELETE from asignaturascursosactivrecursos
                                          WHERE CODASI = :asignatura AND CODACT = :actividad AND CURSO = :curso AND TIPORECURSO = :recurso',
        array('asignatura'=>$asignatura, 'actividad'=>$actividad, 'curso'=>$curso, 'recurso'=>$recurso));
    echo($resultado);
}
function remove_recurso_asignatura_actividad_all($asignatura, $actividad, $curso) {
    $resultado = getDatabase()->execute('DELETE from asignaturascursosactivrecursos
                                          WHERE CODASI = :asignatura AND CODACT = :actividad AND CURSO = :curso',
        array('asignatura'=>$asignatura, 'actividad'=>$actividad, 'curso'=>$curso));
    echo($resultado);
}
function remove_recurso_asignatura_all($asignatura, $curso) {
    $resultado = getDatabase()->execute('DELETE from asignaturascursosactivrecursos
                                          WHERE CODASI = :asignatura AND CURSO = :curso',
        array('asignatura'=>$asignatura, 'curso'=>$curso));
    echo($resultado);
}

function obtener_listado_actividades($asignatura) {
   $actividades = getDatabase()->all('select * from asignaturascursosactiv a, asignaturasactividades b where  a.CODASI = :asignatura
                                      and a.CODACT = b.CODACT and a.CURSO=2014',array(':asignatura'=>$asignatura));
    return salidaJSON($actividades);
}

function remove_tiposAula_asignatura_actividad($asignatura, $actividad, $curso, $tipoAula) {
    $resultado = getDatabase()->execute('UPDATE asignaturascursosactiv SET CODTIPOAULA = -10
                                          WHERE CODASI = :asignatura AND CODACT = :actividad AND CURSO = :curso AND CODTIPOAULA = :tipoaula',
                                            array('asignatura'=>$asignatura, 'actividad'=>$actividad, 'curso'=>$curso, 'tipoaula'=>$tipoAula));
    echo($resultado);
}
function remove_tiposAula_asignatura($asignatura, $curso) {
    $resultado = getDatabase()->execute('UPDATE asignaturascursosactiv SET CODTIPOAULA = -10
                                          WHERE CODASI = :asignatura AND CURSO = :curso',
                                            array('asignatura'=>$asignatura, 'curso'=>$curso));
    echo($resultado);
}

function obtener_listado_recursoss($asignatura, $actividad, $curso) {
    $Recursos = getDatabase()->all('SELECT tip.*, cat.DESCRIP AS CATDESCRIP FROM asignaturascursosactivrecursos a, tiposrecurso tip, categoriasrecurso cat
                WHERE  a.CODACT = :codact AND a.CODASI = :codasi AND a.curso = :curso AND a.TIPORECURSO = tip.TIPORECURSO and tip.CODCATRECURSO = cat.CODCAT', array(':codact'=>$actividad,':codasi'=>$asignatura,':curso'=>$curso));
    return salidaJSON($Recursos);
}


function obtener_listado_tiposAula($asignatura, $actividad, $curso) {
    $tiposAula = getDatabase()->all('SELECT a.CODTIPOAULA, tip.TIPOAULA, tip.DESCRIP, tip.CODEPS FROM asignaturascursosactiv a, tiposaula tip
            where a.CODACT = :codact AND a.CODASI = :codasi AND a.curso = :curso AND a.CODTIPOAULA = tip.CODTIPOAULA', array(':codact'=>$actividad,':codasi'=>$asignatura,':curso'=>$curso));
    return salidaJSON($tiposAula);

}function obtener_listado_tiposAula_noCentralizadas($asignatura, $actividad, $curso) {
    $tiposAula = getDatabase()->all('SELECT a.CODTIPOAULA, tip.TIPOAULA, tip.DESCRIP, tip.CODEPS FROM asignaturascursosactiv a, tiposaula tip
            where a.CODACT = :codact AND a.CODASI = :codasi AND a.curso = :curso AND a.CODTIPOAULA = tip.CODTIPOAULA
        AND tip.TIPOAULA NOT IN ("01AULA","03DEPORTE","02INFORM","05LABDOC","04NO")', array(':codact'=>$actividad,':codasi'=>$asignatura,':curso'=>$curso));
    return salidaJSON($tiposAula);
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

