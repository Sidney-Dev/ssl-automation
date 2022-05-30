<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domains extends Model
{
    protected $guarded = [];
    
    use HasFactory;

    public function let_encrypt_certificate()
    {
        return $this->belongsTo(LetsEncryptCertificate::class);
    }
}
