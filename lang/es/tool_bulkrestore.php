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
 * Language strings (Spanish).
 *
 * @package    tool_bulkrestore
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Restauración masiva de cursos';
$string['bulkrestore:restore'] = 'Restaurar cursos de forma masiva desde archivos de copia de seguridad';

// Upload form.
$string['backupfiles'] = 'Archivos de copia de seguridad (.mbz o .zip)';
$string['backupfiles_help'] = 'Suba uno o varios archivos de copia de seguridad de cursos de Moodle (.mbz) o archivos .zip que agrupen varios archivos .mbz. Cada copia de seguridad encontrada se restaura como un nuevo curso en la categoría de destino. Las restauraciones se ejecutan en segundo plano, por lo que puede cerrar esta página una vez que estén en cola.';
$string['targetcategory'] = 'Categoría de destino';
$string['targetcategory_help'] = 'La categoría en la que se crearán los nuevos cursos restaurados.';
$string['restore'] = 'Restaurar';

// Queueing feedback.
$string['queuednone'] = 'No se encontraron copias de seguridad de cursos (.mbz) en la subida.';
$string['queued'] = '{$a} copia(s) de seguridad de cursos en cola para restaurar. Se restaurarán en segundo plano y aparecerán a continuación a medida que se completen.';

// Status table.
$string['statusheading'] = 'Restauraciones masivas recientes';
$string['statusnone'] = 'Todavía no se ha puesto en cola ninguna restauración masiva.';
$string['col_filename'] = 'Archivo de copia de seguridad';
$string['col_category'] = 'Categoría de destino';
$string['col_course'] = 'Curso restaurado';
$string['col_status'] = 'Estado';
$string['col_detail'] = 'Detalle';
$string['col_time'] = 'En cola';
$string['status_queued'] = 'En cola';
$string['status_running'] = 'Restaurando';
$string['status_done'] = 'Completado';
$string['status_failed'] = 'Fallido';

// Task.
$string['taskname'] = 'Restaurar una copia de seguridad de curso de forma masiva';
$string['restoredcoursename'] = 'Restaurado: {$a}';

// Errors.
$string['precheckfailed'] = 'No se pudo restaurar la copia de seguridad: {$a}';

// Privacy.
$string['privacy:metadata'] = 'El plugin de restauración masiva de cursos no almacena datos personales por sí mismo; los archivos de copia de seguridad subidos son procesados por el subsistema de copia de seguridad y restauración del núcleo.';
