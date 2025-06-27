<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campania extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin'];
}