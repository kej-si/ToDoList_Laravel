<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TodoAttribute extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'todo_attributes';
    protected $fillable = ['name', 'value', 'task_id'];
}