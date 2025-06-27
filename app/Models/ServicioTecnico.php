<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioTecnico extends Model
{
    protected $fillable = ['titulo', 'descripcion', 'estado', 'cliente_id'];
}