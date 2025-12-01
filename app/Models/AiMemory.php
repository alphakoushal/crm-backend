<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMemory extends Model
{
    protected $fillable = ['session_id', 'data'];
    protected $casts = ['data' => 'array'];
}

?>