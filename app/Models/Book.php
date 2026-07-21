<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'isbn', 'amount', 'status'];

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
}
