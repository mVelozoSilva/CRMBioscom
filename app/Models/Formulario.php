<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function campos()
    {
        return $this->hasMany(CampoFormulario::class);
    }
}