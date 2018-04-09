<?php

namespace Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: lov3catch
 * Date: 08.04.18
 * Time: 17:57
 */

class Storage extends Model
{
    public $id;

    public $type;

    public $isGroup;

    public $name;

    protected $table = 'storage';

//    /**
//     * Storage constructor.
//     * @param $id
//     * @param $type
//     * @param $isGroup
//     * @param $name
//     */
//    public function __construct($id, $type, $isGroup, $name)
//    {
//        $this->id = $id;
//        $this->type = $type;
//        $this->isGroup = $isGroup;
//        $this->name = $name;
//    }
}