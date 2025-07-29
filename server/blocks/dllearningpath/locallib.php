<?php

namespace blocks\dllearningpath\locallib;

/**
 * Obtiene una lista de cohortes disponibles en el sistema.
 *
 * @return array Un array asociativo donde las claves son los IDs de los cohortes y los valores son los nombres de los cohortes.
 * @throws \dml_exception
 */
function dllearning_paths_get_cohorts(): array {
    global $DB;

    // Obtiene todos los registros de la tabla 'cohort', ordenados por nombre, y selecciona los campos 'id' y 'name'.
    $cohorts = $DB->get_records('cohort', [], 'name', 'id, name');

    // Inicializa un array para almacenar las opciones de cohortes.
    $cohort_options = [];

    // Recorre cada cohorte y lo agrega al array de opciones.
    foreach ($cohorts as $cohort) {
        $cohort_options[$cohort->id] = $cohort->name;
    }

    // Devuelve el array de opciones de cohortes.
    return $cohort_options;
}

/**
 * Obtiene una lista de cursos disponibles en el sistema.
 *
 * @return array Un array asociativo donde las claves son los IDs de los cursos y los valores son los nombres completos de los
 *     cursos.
 * @throws \dml_exception Si ocurre un error al acceder a la base de datos.
 */
function dllearning_paths_get_courses(): array {
    global $DB;

    // Obtiene todos los registros de la tabla 'course', ordenados por nombre completo, y selecciona los campos 'id' y 'fullname'.
    $courses = $DB->get_records('course', [], 'fullname', 'id, fullname');

    unset($courses[1]);// Elimina el curso "General".

    // Inicializa un array para almacenar las opciones de cursos.
    $courses_options = [];

    // Recorre cada curso y lo agrega al array de opciones.
    foreach ($courses as $course) {
        $courses_options[$course->id] = $course->fullname;
    }

    // Devuelve el array de opciones de cursos.
    return $courses_options;
}

/**
 * Obtiene una lista de rutas de aprendizaje disponibles en el sistema.
 *
 * @return array Un array asociativo donde las claves son los IDs de las rutas de aprendizaje y los valores son los nombres de las
 *     rutas.
 * @throws \dml_exception Si ocurre un error al acceder a la base de datos.
 */
function dllearning_paths_get_path(): array {
    global $DB;

    $path = $DB->get_records('dl_learning_path', [], 'name', 'id, name');
    $path_options = [];
    foreach ($path as $p) {
        $path_options[$p->id] = $p->name;
    }
    return $path_options;
}

function dllearning_paths_get_order($path_id): int {
    global $DB;

    $path = $DB->get_records('dl_learning_path_stage', ['learning_path_id' => $path_id], 'sort desc', 'sort');

    if (empty($path)) {
        $order = 1;
    } else {
        $order = $path[array_key_first($path)]->sort + 1;
    }

    return $order;
}

/**
 * Inscribe a los usuarios de los cohortes especificados en una ruta de aprendizaje.
 *
 * @param string $cohorts Una cadena JSON que contiene los IDs de los cohortes.
 * @param int $path_id El ID de la ruta de aprendizaje.
 * @throws \invalid_parameter_exception
 * @throws \required_capability_exception
 */
function dllearning_paths_enrol(string $cohorts, int $path_id) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/cohort/externallib.php');
    if ($cohorts = json_decode($cohorts)) {
        $cohort = \core_cohort_external::get_cohort_members($cohorts);
        if (is_array($cohort)) {
            foreach ($cohort as $key) {
                $userid = $key['userids'];

                if (sizeof($userid) > 0) {
                    foreach ($userid as $user) {
                        $path_user =
                            $DB->get_record('dl_learning_path_users', ['userid' => $user, 'learning_path_id' => $path_id],
                                'id', IGNORE_MISSING);
                        if (!$path_user) {
                            $data = new \stdClass();
                            $data->userid = $user;
                            $data->learning_path_id = $path_id;
                            $data->timeenrolled = time();
                            $DB->insert_record('dl_learning_path_users', $data);
                        }
                    }
                }
            }
        }
    }
}

/**
 * @throws \dml_exception
 */
function dllearning_paths_unenrol(int $path_id, int $userid = 0) {
    global $DB;
    if ($userid = 0) {
        $DB->delete_records('dl_learning_path_users', ['learning_path_id' => $path_id]);
    } else {
        $DB->delete_records('dl_learning_path_users', ['learning_path_id' => $path_id, 'userid' => $userid]);
    }
}

/**
 * Actualiza los cohortes en las rutas
 *
 * @param int $pathid
 * @param string $old El nombre del cohorte antiguo.
 * @param string $new El nombre del cohorte nuevo.
 */
function dllearning_paths_update_cohorts(int $pathid, string $old, string $new) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/cohort/externallib.php');

    $old_arr = json_decode($old) ?? [];// 123
    $new_arr = json_decode($new) ?? [];//3

    $diff_new = array_diff($old_arr, $new_arr);//12 borrar

    if (sizeof($diff_new) > 0) {
        $cohort_members = \core_cohort_external::get_cohort_members($diff_new);
        if (sizeof($cohort_members) > 0) {
            foreach ($cohort_members as $members) {
                $userids = $members['userids'];

                if (sizeof($userids) > 0) {
                    foreach ($userids as $uid) {
                        $DB->delete_records('dl_learning_path_users', ['learning_path_id' => $pathid, 'userid' => $uid]);
                    }
                }
            }
        }
    }
}