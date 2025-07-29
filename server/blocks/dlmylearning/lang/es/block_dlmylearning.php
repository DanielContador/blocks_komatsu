<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_dlslider', language 'en', branch
 *
 * @package   block_dlmylearning
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dlmylearning:addinstance'] = 'Agregar nuevo bloque My Aprendizaje';
$string['dlmylearning:myaddinstance'] = 'Agregar nuevo bloque My Aprendizaje al Dashboard';
$string['dlmylearning'] = 'DL Mi aprendizaje block';
$string['pluginname'] = 'DL Mi aprendizaje';
$string['userinfo'] = 'Mi aprendizaje';
$string['my_courses'] = 'Mis cursos';
$string['mycourseslabel'] = 'Texto "Mis cursos" personalizado';
$string['my_programs'] = 'Mis programas';
$string['myprogramslabel'] = 'Texto "Mis programas" personalizado';
$string['my_certificates'] = 'Mis certificaciones';
$string['courses'] = 'cursos';
$string['of'] = 'de';
$string['search_courses'] = 'Buscar Cursos';
$string['search_programs'] = 'Buscar Programas';
$string['search_certificates'] = 'Buscar Certificaciones';
$string['showexpiringcourses'] = 'Cursos en proceso';
$string['showexpiringcourseslabel'] = 'Texto "En proceso de aprendizaje" personalizado';
$string['showcourses'] = 'Mis Cursos';
$string['completedcourses'] = 'Cursos finalizados';
$string['showcompletedcourseslabellabel'] = 'Texto "Cursos finalizados" personalizado';
$string['showcompletedcoursesblock'] = 'Cursos finalizados';
$string['showwithoutprogresscourseslabel'] = 'Texto "Cursos No iniciados" personalizado';
$string['withoutprogresscourses'] = 'Cursos No iniciados';
$string['showwithoutprogresscourses'] = 'Cursos no iniciados';
$string['showcertificates'] = 'Mostrar bloque Mis Certificados';
$string['showprograms'] = 'Mis Programas';
$string['showsectionyes'] = 'Si';
$string['showsectionnot'] = 'No';
$string['courses'] = 'Cursos';
$string['programs'] = 'Programas';
$string['certifications'] = 'Certificaciones';
$string['showmodule'] = 'Mostrar Módulo';
$string['gridformat'] = 'Formato del Grid';
$string['generalgrid'] = 'Grid Genérico';
$string['coursespecificgrid'] = 'Grid de Cursos';
$string['programspecificgrid'] = 'Grid de Programas';

$string['pending'] = 'Pendientes';
$string['expiring'] = 'Por Vencer';
$string['expired'] = 'Vencido';
$string['current'] = 'Actuales';
$string['historical'] = 'Histórico';
$string['modulestatus'] = 'Estado de los items del módulo';

$string['coursespending'] = 'Cursos Pendientes';
$string['expiringcourses'] = 'Cursos Por Vencer';
$string['currentcourses'] = 'Cursos Actuales';
$string['historicalcourses'] = 'Cursos Históricos';

$string['programspending'] = 'Programas Pendientes';
$string['expiringprograms'] = 'Programas Por Vencer';
$string['currentprograms'] = 'Programas Actuales';
$string['historicalprograms'] = 'Programas Históricos';

$string['countcards'] = 'Cantidad de Items del Grid';
$string['countcards_help'] = 'Texto de Ayuda';

$string['recentcourses'] = 'Cursos Recientes';
$string['recentprograms'] = 'Programas Recientes';
$string['recentcertifications'] = 'Certificaciones Recientes';
$string['gotocourses'] = 'Ver otros cursos';
$string['gotoprograms'] = 'Ver otros programas';
$string['gotocertifications'] = 'Ver otras certificaciones';
$string['stateinprogress'] = 'En curso';
$string['statecomplete'] = 'Completo';
$string['statenotstarted'] = 'No iniciado';

$string['maxitems'] = 'Cantidad máxima de elementos';
$string['showcompletedcourses'] = 'Mostrar cursos completados';
$string['showcoursestimeend'] = 'Mostrar cursos vencidos';
$string['showprogramscompleted'] = 'Mostrar programas completados';
$string['showprogramstimedue'] = 'Mostrar programas vencidos';
$string['showcompletedprograms'] = 'Mostrar programas finalizados';

$string['unlimited'] = 'Ilimitados';
$string['showprogramcourses'] = 'Mostrar cursos de programas';
$string['showprogramcourses_help'] =
    'Mostrar cursos pertenecientes a programas vigentes donde esté asignado el usuario pero su matriculación es distinta a por programa (Ejem: Por Audiencia).';
$string['orderblocks_help'] = 'Define el orden en el que aparecen los bloques';
$string['orderblocks'] = 'Ordenar bloques';
$string['blockedition'] = '<h3>Edición de bloques</h3> <br> <hr>';

//Help
$string['showexpiringcourses_help'] = 'Controla la visibilidad y el nombre del bloque "Cursos en proceso". 
Un curso se considera en proceso cuando su porcentaje de avance está entre el 1% y el 99%, y su estado es 
"en progreso".';
$string['showcourses_help'] = 'Controla la visibilidad y el nombre del bloque "Mis cursos". Este bloque combina los bloques 
"Cursos no iniciados" y  "Cursos finalizados", con la opción adicional de mostrar o no los cursos que pertenecen a programas.';
$string['withoutprogresscourses_help'] = 'Controla la visibilidad y el nombre del bloque "Cursos no iniciados". 
Un curso se considera no iniciado cuando su porcentaje de avance es 0% y su estado es "No iniciado".';
$string['showcompletedcoursesblock_help'] = 'Controla la visibilidad y el nombre del bloque "Cursos finalizados". 
Un curso se considera finalizado cuando su porcentaje de avance es del 100% y su estado es "completado".';
$string['showprograms_help'] = 'Controla la visibilidad y el nombre del bloque "Mis programas". Este bloque muestra 
todos los programas del usuario, con la opción adicional de mostrar o no los programas finalizados.';