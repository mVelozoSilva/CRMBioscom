<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles básicos según el roadmap
        $roles = [
            'Vendedor',
            'Asistente de Ventas', 
            'Encargado de Cobranza',
            'Asistente de Cobranza',
            'Encargado de Servicio Técnico y Logística',
            'Personal Servicio Técnico y Logística',
            'Jefe de Ventas',
            'Gerente General',
            'Administrador General'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Asignar rol por defecto al usuario admin (ID 1)
        $adminUser = \App\Models\User::find(1);
        if ($adminUser) {
            $adminUser->assignRole('Administrador General');
        }

        // Asignar roles básicos a otros usuarios existentes
        $users = \App\Models\User::where('id', '>', 1)->get();
        foreach ($users as $user) {
            if (!$user->hasAnyRole()) {
                $user->assignRole('Vendedor'); // Rol por defecto
            }
        }
    }
}