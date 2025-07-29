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
 * Form for editing badges block instances.
 *
 * @package    block_dlmylearning
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */
class block_dlmylearning_edit_form extends block_edit_form {
    // Configuraci�n para mostrar cursos que est�n por expirar.
    /**
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        // Mostrar cursos que están por expirar con su etiqueta.
        global $PAGE, $OUTPUT;
        $showIco = $OUTPUT->pix_icon('i/show', get_string('visible', 'moodle'));
        $hideIco = $OUTPUT->pix_icon('i/hide', get_string('hidden', 'moodle'));

        $mform->addElement('html', get_string('blockedition', 'block_dlmylearning'));

        $mform->addGroup([
                             $mform->createElement('advcheckbox', 'config_showexpiringcourses', ''),
                             $mform->createElement('text', 'config_showexpiringcourseslabel',
                                                   get_string('showexpiringcourseslabel', 'block_dlmylearning'))
                         ], 'showexpiringcoursesgroup', get_string('showexpiringcourses', 'block_dlmylearning'), [' '], false);
        $mform->setDefault('config_showexpiringcourses', 1);
        $mform->setDefault('config_showexpiringcourseslabel', get_string('showexpiringcourses', 'block_dlmylearning'));
        $mform->setType('config_showexpiringcourseslabel', PARAM_RAW);
        $mform->addHelpButton('showexpiringcoursesgroup', 'showexpiringcourses', 'block_dlmylearning');

        // Mostrar todos los cursos con su etiqueta.
        $mform->addGroup([
                             $mform->createElement('advcheckbox', 'config_showcourses', '', ''),
                             $mform->createElement('text', 'config_showcourseslabel',
                                                   get_string('mycourseslabel', 'block_dlmylearning')),
                             $mform->createElement('advcheckbox', 'config_showprogramcourses', '',
                                                   get_string('showprogramcourses', 'block_dlmylearning')),
                         ], 'showcoursesgroup', get_string('showcourses', 'block_dlmylearning'), [' '], false);
        $mform->setDefault('config_showcourses', 1);
        $mform->setDefault('config_showcourseslabel', get_string('my_courses', 'block_dlmylearning'));
        $mform->setType('config_showcourseslabel', PARAM_RAW);
        $mform->setDefault('config_showprogramcourses', 0);
        $mform->addHelpButton('showcoursesgroup', 'showcourses', 'block_dlmylearning');

        // Mostrar bloque de cursos en 0% con su etiqueta.
        $mform->addGroup([
                             $mform->createElement('advcheckbox', 'config_withoutprogresscourses', '', ''),
                             $mform->createElement('text', 'config_showwithoutprogresscourseslabel',
                                                   get_string('showwithoutprogresscourseslabel', 'block_dlmylearning'))
                         ], 'withoutprogresscoursesgroup', get_string('showwithoutprogresscourses', 'block_dlmylearning'), [' '],
            false);
        $mform->setDefault('config_withoutprogresscourses', 0);
        $mform->setDefault('config_showwithoutprogresscourseslabel', get_string('withoutprogresscourses', 'block_dlmylearning'));
        $mform->setType('config_showwithoutprogresscourseslabel', PARAM_RAW);
        $mform->addHelpButton('withoutprogresscoursesgroup', 'withoutprogresscourses', 'block_dlmylearning');

        // Mostrar bloque de cursos en 100% con su etiqueta.
        $mform->addGroup([
                             $mform->createElement('advcheckbox', 'config_completedcoursesblock', '', ''),
                             $mform->createElement('text', 'config_completedcourseslabel',
                                                   get_string('showcompletedcourseslabellabel', 'block_dlmylearning'))
                         ], 'completedcoursesblockgroup', get_string('showcompletedcoursesblock', 'block_dlmylearning'), [' '],
            false);
        $mform->setDefault('config_completedcoursesblock', 0);
        $mform->setDefault('config_completedcourseslabel', get_string('completedcourses', 'block_dlmylearning'));
        $mform->setType('config_completedcourseslabel', PARAM_RAW);
        $mform->addHelpButton('completedcoursesblockgroup', 'showcompletedcoursesblock', 'block_dlmylearning');

        // Mostrar bloque programas con su etiqueta.
        $mform->addGroup([
                             $mform->createElement('advcheckbox', 'config_showprograms', '', ''),
                             $mform->createElement('text', 'config_showprogramcourseslabel',
                                                   get_string('myprogramslabel', 'block_dlmylearning')),
                             $mform->createElement('advcheckbox', 'config_showcompletedprograms', '',
                                                   get_string('showcompletedprograms', 'block_dlmylearning'))
                         ], 'showprogramsgroup', get_string('showprograms', 'block_dlmylearning'), [' '], false);
        $mform->setDefault('config_showprograms', 1);
        $mform->setDefault('config_showprogramcourseslabel', get_string('showprograms', 'block_dlmylearning'));
        $mform->setType('config_showprogramcourseslabel', PARAM_RAW);
        $mform->setDefault('config_showcompletedprograms', 0);
        $mform->addHelpButton('showprogramsgroup', 'showprograms', 'block_dlmylearning');

        // Orden
        $defaultValue = "Cursos en proceso,\nCursos no iniciados,\nCursos finalizados,\nMis cursos,\nMis programas";
        $mform->addElement('textarea', 'config_order', get_string('orderblocks', 'block_dlmylearning'),
                           'style="display: none"');
        $mform->setDefault('config_order', $defaultValue);
        $mform->setType('config_order', PARAM_RAW);
        $mform->addHelpButton('config_order', 'orderblocks', 'block_dlmylearning');

        //Nuevo orden
        $sortable = '<div class="fitem">
                     <div class="fitemtitle"></div>
                     <ul class="felement" id="sortableList" style="list-style: none; 
                     padding: 0; width: 20%; margin: -40px 0 0px 50px;"></ul>
                     </div>
                     <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
                     <script>
                     const defaultOrder = document.getElementById("id_config_order").value.split(",\\n");
                     const sortableList = document.getElementById("sortableList");
                     defaultOrder.forEach(item => {
                     const listItem = document.createElement("li");
                     listItem.textContent = item.trim();
                     listItem.style.cssText = "padding: 8px; border: 1px solid #ccc; margin-bottom: 4px; cursor: move;";
                     sortableList.appendChild(listItem);
                     });

                     const sortable = new Sortable(sortableList, {
                     animation: 150,
                     onEnd: function () {
                     const order = Array.from(document.querySelectorAll("#sortableList li"))
                    .map(item => item.textContent)
                    .join(",\\n");
                     document.getElementById("id_config_order").value = order;
                     }
                     });
                     </script>
                     ';

        $mform->addElement('html', $sortable);
        $PAGE->requires->js_init_code("
        const toggleVisibility = (checkboxId, labelId, additionalId) => {
        const showIcon = `$showIco`;
        const hideIcon = `$hideIco`;
        const checkbox = document.getElementById(checkboxId);
        const label = document.getElementById(labelId);
        const visibilityLabel = document.createElement('label');
        const additionalOption = document.getElementById(additionalId);

        visibilityLabel.innerHTML = checkbox.checked ? hideIcon : showIcon;
        visibilityLabel.setAttribute('for', checkbox.id);
        checkbox.parentNode.insertBefore(visibilityLabel, checkbox);
        checkbox.style.display = 'none';
        label.disabled = !checkbox.checked;
        if(additionalOption){
        const additionalLabel = document.querySelector(`label[for='\${additionalOption.id}']`);
        additionalLabel.style.display = !checkbox.checked ? 'none' : 'inline'
        additionalOption.disabled = !checkbox.checked;
        additionalOption.style.display = !checkbox.checked ? 'none' : 'inline'
        }

        checkbox.addEventListener('change', function () {
        label.disabled = !this.checked;
        visibilityLabel.innerHTML = this.checked ? hideIcon : showIcon;
        if(additionalOption){
        const additionalLabel = document.querySelector(`label[for='\${additionalOption.id}']`);
        additionalLabel.style.display = !checkbox.checked ? 'none' : 'inline'
        additionalOption.disabled = !checkbox.checked;
        additionalOption.style.display = !checkbox.checked ? 'none' : 'inline'
        }
        });
        };

        toggleVisibility('id_config_showexpiringcourses', 'id_config_showexpiringcourseslabel', '');
        toggleVisibility('id_config_showcourses', 'id_config_showcourseslabel', 'id_config_showprogramcourses');
        toggleVisibility('id_config_withoutprogresscourses', 'id_config_showwithoutprogresscourseslabel', '');
        toggleVisibility('id_config_completedcoursesblock', 'id_config_completedcourseslabel', '');
        toggleVisibility('id_config_showprograms', 'id_config_showprogramcourseslabel', 'id_config_showcompletedprograms');
        ");
    }
}
