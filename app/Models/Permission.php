<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name','label'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }
}
