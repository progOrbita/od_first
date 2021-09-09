<?php
declare (strict_types=1);

namespace OrbitaDigital\OdFirst;

class Fields{
    /**
     * Create a field for helperform.
     * Required values: type, label and name
     * Optional values: desc, class, disabled (default false), required (default false)
     * @return array contains the parameters sent.
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
    /**
     * Create a field for helperlist
     * Required values: title, width, type
     * Optional values: filter_type, suffix, order (true default), search (true default), have_filter (false default)
     */
    public static function createTableField(string $title, int $width, string $type, string $filter_type=null, string $suffix=null, bool $order=true, bool $search=true, bool $have_filter=false){
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
    /**
     * Create a button for helperform
     * Required values: id, name and title. 
     * Optional values: class
     */
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