<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domains extends Model
{
    protected $fillable = [ 'name', 'status', 'domain', 'toplevel'];
    use HasFactory;
}
