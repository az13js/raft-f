<?php

class demo extends entity
{
    public $structs = [
        'column1' => '',
        'column2' => '',
    ];

    public function __construct()
    {/*{{{*/
        
    }/*}}}*/

    public static function create($column1, $column2)
    {/*{{{*/
        $demo =  parent::init();

        $demo->column1 = $column1;
        $demo->column2 = $column1;

        return $demo;
    }/*}}}*/
}
