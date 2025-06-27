<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampoFormulario extends Model
{
    protected $fillable = ['formulario_id', 'tipo', 'nombre', 'etiqueta', 'requerido'];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class);
    }
}