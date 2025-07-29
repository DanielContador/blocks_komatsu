<?php

namespace local_dlservices\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;

class program extends type_resolver {
    /**
     * @param string $field - The field being requested
     * @param $source - In the case, source will be our `item` entity class as it's what's returned from the query resolver
     * @param array $args
     * @param execution_context $ec
     * @return mixed|void
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if($field === 'id') {
            return $source->id;
        }
        if($field === 'fullname') {
            return $source->fullname;
        }
        if($field === 'shortname') {
            return $source->shortname;
        }
        if($field === 'summary') {
            return $source->summary;
        }
        if($field === 'category') {
            return $source->category;
        }
        if($field === 'link') {
            return $source->link;
        }
        if($field === 'imageUrl') {
            return $source->imageUrl;
        }
        if($field === 'duration') {
            return $source->duration;
        }
        if($field === 'progress') {
            return $source->progress;
        }
        if($field === 'status') {
            return $source->status;
        }
        if($field === 'isNew') {
            return $source->isNew;
        }
        if($field === 'itemType') {
            return $source->itemType;
        }

        return null;
    }
}
