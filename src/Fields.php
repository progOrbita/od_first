<?php
declare (strict_types=1);

namespace OrbitaDigital\OdFirst;

class Fields{
    /**
     * Create a field for helperform
     */
    public static function createFormField(string $type, string $label, string $name, string $desc=null, string $class=null, bool $disabled=false, bool $required=false){
        return [ 
            'type' => $type,
            'label' => $label, 
            'name' => $name, 
            'desc' => $desc, 
            'class' => $class, 
            'disabled' => $disabled, 
            'required' => $required
        ];
    }
    public static function createTableField(string $title, int $width, string $type, string $filter_type=null, string $suffix=null, bool $order=false, bool $search=true, bool $have_filter=false){
        return [ 
            'title' => $title,
            'width' => $width,
            'type' => $type,
            'filter_type' => $filter_type,
            'suffix' => $suffix,
            'orderby' => $order,
            'search' => $search,
            'havingFilter' => $have_filter,
        ];
    }
    public static function createButton(string $id, string $name, string $title, string $class=null){
        return [ 
            'type' => 'button',
            'id' => $id,
            'name' => $name,
            'title' => $title,
            'class' => $class
        ];
    }
}