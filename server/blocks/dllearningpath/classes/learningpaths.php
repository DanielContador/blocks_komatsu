<?php

namespace blocks\dllearningpath\classes;

class learningpaths {
    /** @var \stdClass */
    private array $odd;
    private array $even;
    private array $stages;
    private int $pathId;

    public function __construct() {
        $this->odd = [];
        $this->even = [];
        $this->stages = [];
        $this->pathId = 0;

        $this->last_user_path();
        if ($this->pathId !== 0) {
            $this->get_stages();
            if (!empty($this->stages)) {
                $this->find_course_status();
                $this->find_course_active();
                $this->set_stage_pos();
                $this->sort_courses();
            }
        }
    }

    private function last_user_path() {
        global $DB, $USER;

        $sql = "SELECT learning_path_id FROM {dl_learning_path_users} 
                WHERE userid = :userid ORDER BY timeenrolled DESC LIMIT 1";
        $params = ['userid' => $USER->id];
        $userPath = $DB->get_record_sql($sql, $params);
        $this->pathId = $userPath->learning_path_id ?? 0;
    }

    private function get_stages() {
        global $DB;
        $tmp_stages = [];
        $stages = $DB->get_records('dl_learning_path_stage', ['learning_path_id' => $this->pathId], 'sort',
            'name, id, sort, course_sets');
        if (!empty($stages)) {
            foreach ($stages as $stage) {
                $stage->course_sets = json_decode($stage->course_sets);
                if (!empty($stage->course_sets)) {
                    foreach ($stage->course_sets as $key => $courseId) {
                        $course = $DB->get_record('course', ['id' => $courseId], '*');

                        // Calculate duration
                        $duration = "No disponible";
                        $field_duration = $DB->get_record('course_info_field', ['shortname' => 'duracion']);
                        if ($field_duration) {
                            $get_duration = $DB->get_record('course_info_data',
                                ['fieldid' => $field_duration->id ?? null, 'courseid' => $courseId]);
                            if ($get_duration) {
                                $duration = $get_duration->data;
                            }
                        }
                        $stage->course_sets[$key] =
                            ['name' => $course->fullname, 'id' => $courseId, 'image' => course_get_image($course),
                                'duracion' => $duration];
                    }
                } else {
                    $stage->course_sets = [];
                }
                $tmp_stages[] = $stage;
            }
        }
        $this->stages = $tmp_stages;
    }

    private function set_stage_pos() {
        if (!empty($this->stages)) {
            $order = 1;
            foreach ($this->stages as $stage) {
                switch ($order) {
                    case $order % 2 !== 0:
                        $this->odd[] = ['number' => $order > 9 ? $order : '0' . $order, 'name' => $stage->name,
                            'courses' => $stage->course_sets, 'active_stage' => $stage->active_stage ?? false];
                        $order++;
                        break;
                    case $order % 2 == 0:
                        $this->even[] = ['number' => $order > 9 ? $order : '0' . $order, 'name' => $stage->name,
                            'courses' => $stage->course_sets, 'active_stage' => $stage->active_stage ?? false];
                        $order++;
                        break;
                    default:
                        break;
                }
            }
        }
    }

    private function sort_courses() {
        $pos = 0;
        $max_pos = max([sizeof($this->odd), sizeof($this->even)]);
        while ($pos < $max_pos) {
            $odd_courses = $this->odd[$pos]['courses'] ?? [];
            $even_courses = $this->even[$pos]['courses'] ?? [];

            $this->odd[$pos]['courses'] = $even_courses;
            if (!empty($even_courses) && sizeof($even_courses) > 2) {
                $this->odd[$pos]['modalCourses'] = $even_courses;
                $this->odd[$pos]['haveCourses'] = true;
                $this->odd[$pos]['totalCourses'] = sizeof($even_courses) - 2;
            } else {
                $this->odd[$pos]['haveCourses'] = false;
            }
            $this->even[$pos]['courses'] = $odd_courses;
            if (!empty($odd_courses) && sizeof($odd_courses) > 2) {
                $this->even[$pos]['modalCourses'] = $odd_courses;
                $this->even[$pos]['haveCourses'] = true;
                $this->even[$pos]['totalCourses'] = sizeof($odd_courses) - 2;
            } else {
                $this->even[$pos]['haveCourses'] = false;
            }

            $pos++;
        }

        foreach ($this->odd as $key => $odd) {
            $current = [];
            $next = [];

            if (sizeof($odd['courses']) > 1) {
                foreach ($odd['courses'] as $course) {
                    if ($course['current']) {
                        $current = $course;
                    } else if (!$course['isCompleted']) {
                        $next[] = $course;
                    } else {
                        $courseComplete[] = $course;
                    }
                }

                if (!empty($current)) {
                    $this->odd[$key]['courses'] = [$current, $next[0] ?? $courseComplete[0]];
                } else {
                    if (empty($next)) {
                        $tmp_course = [];
                        $tmp_course[] = $this->odd[$key]['courses'][array_key_last($this->odd[$key]['courses'])];
                        $tmp_course[] = $this->odd[$key]['courses'][array_key_last($this->odd[$key]['courses']) - 1];
                        $this->odd[$key]['courses'] = $tmp_course;
                    } else {
                        $this->odd[$key]['courses'] = [$next[0] ?? [], $next[1] ?? $courseComplete[0]];
                    }
                }
            }
        }

        foreach ($this->even as $key => $even) {
            $current = [];
            $next = [];

            if (sizeof($even['courses']) > 1) {
                foreach ($even['courses'] as $course) {
                    if ($course['current']) {
                        $current = $course;
                    } else if (!$course['isCompleted']) {
                        $next[] = $course;
                    } else {
                        $courseComplete[] = $course;
                    }
                }

                if (!empty($current)) {
                    $this->even[$key]['courses'] = [$current, $next[0] ?? $courseComplete[0]];
                } else {
                    if (empty($next)) {
                        $tmp_course = [];
                        $tmp_course[] = $this->even[$key]['courses'][array_key_last($this->even[$key]['courses'])];
                        $tmp_course[] = $this->even[$key]['courses'][array_key_last($this->even[$key]['courses']) - 1];
                        $this->even[$key]['courses'] = $tmp_course;
                    } else {
                        $this->even[$key]['courses'] = [$next[0] ?? [], $next[1] ?? $courseComplete[0]];
                    }
                }
            }

        }

    }

    private function find_course_status() {
        global $USER;
        if (!empty($this->stages)) {
            foreach ($this->stages as $keys => $stage) {
                if (!empty($stage->course_sets)) {
                    foreach ($stage->course_sets as $keyc => $course) {
                        $completion = new \completion_completion(['userid' => $USER->id, 'course' => $course['id']]);

                        if (($completion->get_percentagecomplete() == 100 || $completion->is_complete())) {
                            $course['isCompleted'] = true;
                            $stage->course_sets[$keyc] = $course;
                            $stage->active_stage = true;
                        } else {
                            $course['isCompleted'] = false;
                            $stage->course_sets[$keyc] = $course;

                            if ($keys == 0) {
                                $stage->active_stage = true; // Primera etapa siempre activa
                            } else if ($this->is_stage_completed($this->stages[$keys - 1])) {
                                $stage->active_stage = true; // Si la anterior está completa
                            } else {
                                $stage->active_stage = false;
                            }
                        }
                    }
                    $this->stages[$keys] = $stage;
                }

            }
        }
    }

    private function find_course_active() {
        $first = false;
        $stageCursor = 0;
        if (!empty($this->stages)) {
            foreach ($this->stages as $keys => $stage) {
                if (!empty($stage->course_sets)) {
                    foreach ($stage->course_sets as $keyc => $course) {
                        if (!$course['isCompleted'] && !$first) {
                            $courseid = $course['id'];
                            $course['href'] = "/course/view.php?id=$courseid";
                            $course['current'] = true;
                            $stage->course_sets[$keyc] = $course;
                            $stage->active_stage = true;
                            $first = true;
                            $stageCursor = $keys;
                        } else if ($course['isCompleted'] && !$first) {
                            $courseid = $course['id'];
                            $course['href'] = "/course/view.php?id=$courseid";
                            $stage->course_sets[$keyc] = $course;
                            $stage->active_stage = true;
                        } else {
                            if ($stageCursor == $keys) {
                                $courseid = $course['id'];
                                $course['href'] = "/course/view.php?id=$courseid";
                            } else {
                                $course['href'] = "#block-learning-path";
                                $course['isCompleted'] = false;
                                $stage->active_stage = false;
                            }
                            $course['current'] = false;
                            $stage->course_sets[$keyc] = $course;
                        }
                    }
                    $this->stages[$keys] = $stage;
                }
            }
        }
    }

    public function is_stage_completed($stage) {
        foreach ($stage->course_sets as $course) {
            if (!$course['isCompleted']) {
                return false;
            }
        }
        return true;
    }

    public function get_user_path() {
        return $this->pathId;
    }

    public function get_body_stages() {
        return $this->stages ?? false;
    }

    public function get_odd() {
        return $this->odd;
    }

    public function get_even() {
        return $this->even;
    }

}