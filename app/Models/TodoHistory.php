<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoHistory extends Model
{
    use HasFactory;

    protected $fillable = ['todo_id', 'action', 'changes', 'action_at'];

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }
}