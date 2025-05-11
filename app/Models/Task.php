<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\HybridRelations;

class Task extends Model
{
    use HasFactory, HybridRelations;

    /**
     * The table associated with the model.
     */
    protected $table = 'to_do_list';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'title',
        'description',
        'due_date',
        'status',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes for the task.
     */
    public function attributes()
    {
        return $this->hasMany(TodoAttribute::class, 'task_id', 'id');
    }
}
