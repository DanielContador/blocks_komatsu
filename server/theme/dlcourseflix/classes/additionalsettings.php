<?php

use core\theme\file\helper;
use core\theme\file\theme_file;
use theme_config;
use totara_form\file_area;

/**
 * Theme appearance settings manager.
 */
class theme_dlcourseflix_additionalsettings {

    /** @var theme_config */
    private $theme_config;

    /** @var int */
    private $tenant_id;

    /** @var array */
    private $valid_properties = [];

    /** @var array */
    private $fileareas = [];

    /**
     * settings constructor.
     *
     * @param theme_config $theme_config
     * @param int $tenant_id
     */
    public function __construct(theme_config $theme_config, int $tenant_id) {
        $this->theme_config = $theme_config;
        $this->tenant_id = $tenant_id;
        $this->valid_properties = ['column1' => 'text', 
                                    'column2' => 'text',
                                    'column3' => 'text',
                                    'footerweb' => 'text',
                                    'footerinstagramurl' => 'text',
                                    'footerlinkedinurl' => 'text'];
        $this->fileareas = ['footerlogo'];
    }

    public function set_tenantid($tenant_id) {
        $this->tenant_id = $tenant_id;
    }

    /**
     * Get setting categories for theme.
     *
     * @return array|mixed
     */
    public function get_categories($returndefault=false): array {
        global $DB;

        $categories = array();
        // Get all variables for site.
        $values = $DB->get_field('config_plugins', 'value', [
            'name' => "tenant_0_additionalsettings",
            'plugin' => "theme_dlcourseflix"
        ]);
        if (!empty($values) && ($returndefault || $this->tenant_id == 0)) {
            $theme_categories = json_decode($values, true);
            $this->merge_categories($categories, $theme_categories);
        }

        // Get all variables for current tenant and override site.
        if ($this->tenant_id > 0) {
            $values = $DB->get_field('config_plugins', 'value', $this->get_config_parameters($this->tenant_id));
            if (!empty($values)) {
                $theme_categories = json_decode($values, true);
                $this->merge_categories($categories, $theme_categories);
            }
        }

        return $categories;
    }

    public function get_rawsettings() {
        $tenant_id = $this->tenant_id;
        $categories = $this->get_categories(false);
        $tenant_settings = array();

        foreach ($categories as $category) {
            // Update category if found.
            if ($category['name'] === 'additional') {
                $properties = $category['properties'];

                foreach($properties as $property) {
                    $propname = $property['name'];
                    $fieldname = "tenant_{$tenant_id}_{$propname}";
                    $tenant_settings[$fieldname] = $property['value'];
                }

                break;
            }
        }

        $fileareas = $this->fileareas;
        $itemid = $this->get_itemid();

        foreach($fileareas as $filearea) {
            $fieldname = "tenant_{$tenant_id}_{$filearea}_filemanager";
            $tenant_settings[$fieldname] = new file_area(context_system::instance(),
                                                        'theme_dlcourseflix',
                                                        $filearea,
                                                        $itemid);
        }
        return $tenant_settings;
    }

    /**
     * Overwrite values in array1 with values in array2.
     *
     * @param array $categories1
     * @param array $categories2
     */
    private function merge_categories(array &$categories1, array $categories2) {
        foreach ($categories2 as $category2) {
            foreach ($categories1 as &$category1) {
                if ($category1['name'] === $category2['name']) {
                    $this->merge_properties(
                        $category1['properties'],
                        $category2['properties']
                    );
                    continue 2;
                }
            }
            $categories1[] = $category2;
        }
    }

    /**
     * Overwrite values in array1 with values in array2.
     *
     * @param array $props1
     * @param array $props2
     */
    private function merge_properties(array &$props1, array $props2) {
        foreach ($props2 as $prop2) {
            foreach ($props1 as &$prop1) {
                if ($prop1['name'] === $prop2['name']) {
                    $prop1['value'] = $prop2['value'];
                    continue 2;
                }
            }
            $props1[] = $prop2;
        }
    }

    private function get_url($file) {
        if($file) {
            return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                '/',
                $file->get_filename()
            );
        }
        return null;
    }

    public function get_currentfile_url($filearea) {
        // footer_logo
        if($filearea && in_array($filearea, $this->fileareas)) {
            $tenant_id = $this->tenant_id;
            $itemid = $this->get_itemid();
            $filename = "tenant_{$tenant_id}_footerimage.png";

            $fs = get_file_storage();
            $context = context_system::instance();
            $file = $fs->get_file($context->id, 'theme_dlcourseflix', $filearea, $itemid, '/', $filename);
                
            // if(!$file) {
            //     $itemid = $this->get_itemid(true);
            //     $filename = "tenant_0_footerimage.png";
            //     $file = $fs->get_file($context->id, 'theme_dlcourseflix', $filearea, $itemid, '/', $filename);
            // }
            return $this->get_url($file);
        }
        return null;
    }

    /**
     * @param array $categories
     */
    public function update_categories($data): void {
        global $DB;

        $categories = $this->parse_formdata($data);
        $condition = $this->get_config_parameters();

        // Update per category if found, otherwise insert new record.
        $cats = $categories;
        if ($record = $DB->get_record('config_plugins', $condition)) {
            $cats = json_decode($record->value, true);
            foreach ($categories as $category) {
                // Update category if found.
                foreach ($cats as &$cat) {
                    if ($cat['name'] === $category['name']) {
                        $cat['properties'] = $category['properties'];
                        continue 2;
                    }
                }
                // Add new category if not found.
                $cats[] = $category;
            }
        }

        set_config($condition['name'], json_encode($cats), $condition['plugin']);
    }

    /**
     * @return int $itemid
     */
    public function get_itemid($defaultitemid=false) {
        global $DB;

        $tenant_id = $defaultitemid?0:$this->tenant_id;
        $plugin = "theme_dlcourseflix";
        $name = "tenant_{$tenant_id}_additionalsettings";

        // Always make sure that there is a record representing this config.
        if (!get_config($plugin, $name)) {
            set_config($name, '{}', $plugin);
        }

        $context = context_system::instance();
        $itemid = $DB->get_field(
            'config_plugins',
            'id',
            [
                'plugin' => $plugin,
                'name' => $name,
            ]
        );

        return $itemid;     
    }


    /**
     * Get a specific property.
     *
     * @param string $category
     * @param string $property
     *
     * @return array|null
     */
    public function get_property(string $category, string $property, ?array $categories = null): ?array {
        $categories = $categories ?? $this->get_categories();
        foreach ($categories as $cat) {
            if ($cat['name'] === $category) {
                foreach ($cat['properties'] as $prop) {
                    if ($prop['name'] === $property) {
                        return $prop;
                    }
                }
                break;
            }
        }

        return null;
    }

    /**
     * Confirm if a user has the capability required to manage a theme file.
     *
     * @param theme_file $theme_file
     *
     * @return bool
     */
    public function can_manage(theme_file $theme_file): bool {
        $context = $theme_file->get_context();
        if ($context instanceof \context_tenant) {
            $tenant = \core\record\tenant::fetch($context->tenantid);
            $context = \context_coursecat::instance($tenant->categoryid);
        }
        return has_capability('totara/tui:themesettings', $context);
    }

    /**
     * @param int|null $tenant_id
     * @return array
     */
    private function get_config_parameters(?int $tenant_id = null): array {
        $tenant_id = $tenant_id ?? $this->tenant_id;

        return [
            'name' => "tenant_{$tenant_id}_additionalsettings",
            'plugin' => "theme_dlcourseflix"
        ];
    }

    /**
     * @param array|null $formdata
     * @return array
     */
    private function parse_formdata($formdata) {
        
        $tenant_id = $this->tenant_id;
        $properties = [];
        if(!empty($formdata)) {
            foreach($this->valid_properties as $property => $typeproperty) {
                $fieldname = "tenant_{$tenant_id}_{$property}";
                
                if(isset($formdata->{$fieldname})) {
                    $properties[] = ["name" => $property, "type" => $typeproperty, "value" => $formdata->{$fieldname}];
                }
            }
        }

        if($properties) {
            return [["name" => "additional", "properties" => $properties]];
        }
        return [];
    }
}