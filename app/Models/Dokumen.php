<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $fillable = [
        'jenis_dokumen', 'no_dokumen', 'nama', 'file_dokumen'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
