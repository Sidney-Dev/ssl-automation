<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property int $lets_encrypt_certificate_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */

class Domains extends Model
{
    protected $guarded = [];
    
    use HasFactory;

    public function let_encrypt_certificate()
    {
        return $this->belongsTo(LetsEncryptCertificate::class);
    }
}
