<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $fillable = ['titulo', 'mensaje', 'tipo', 'urgente', 'visto', 'usuario_id'];
}