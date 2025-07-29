<?php

namespace local_dlservices\webapi\resolver\type;

use core\webapi\execution_context;
use core\webapi\type_resolver;

class item extends type_resolver {
    /**
     * @param string $field - The field being requested
     * @param $source - In the case, source will be our `item` entity class as it's what's returned from the query resolver
     * @param array $args
     * @param execution_context $ec
     * @return mixed|void
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if ($field === 'id') {
            return $source->id;
        }
        if ($field === 'shortname') {
            return $source->shortname;
        }
        if ($field === 'fullname') {
            return $source->fullname;
        }
        if ($field === 'summary') {
            return $source->summary;
        }
        if ($field === 'mobile_image') {
            return $source->mobile_image;
        }
        if ($field === 'url_view') {
            return $source->url_view;
        }
        if ($field === 'imageUrl') {
            return $source->imageUrl;
        }
        if ($field === 'link') {
            return $source->link;
        }
        if ($field === 'progress') {
            return $source->progress;
        }
        if ($field === 'category') {
            return $source->category;
        }
        if ($field === 'duration') {
            return $source->duration;
        }
        if ($field === 'status') {
            return $source->status;
        }
        if ($field === 'top') {
            return $source->top;
        }
        if ($field === 'isNew') {
            return $source->isNew;
        }
        if ($field === 'itemType') {
            return $source->itemType;
        }
        if ($field === 'gifImage') {
            return $source->gifImage;
        }
        if ($field === 'recent') {
            return $source->recent;
        }

        return null;
    }
}
