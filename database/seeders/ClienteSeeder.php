<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Contacto;

class ClienteSeeder extends Seeder
{
    /**
     * Ejecutar el seeder - Versión segura que evita duplicados
     */
    public function run(): void
    {
        // Verificar si ya hay clientes para evitar duplicados
        if (Cliente::count() > 0) {
            echo "⚠️  Ya existen " . Cliente::count() . " clientes en la base de datos.\n";
            echo "🔄 ¿Quieres continuar agregando más? (Los emails deben ser únicos)\n";
            
            // Si ya hay clientes, usar emails diferentes
            $this->crearClientesAlternativos();
        } else {
            // Si no hay clientes, crear los originales
            $this->crearClientesOriginales();
        }
    }

    private function crearClientesOriginales()
    {
        echo "📝 Creando clientes originales...\n";

        // Cliente 1: Hospital Regional
        $cliente1 = Cliente::create([
            'nombre_institucion' => 'Hospital Regional de Santiago',
            'nombre_contacto' => 'Dr. Carlos Mendoza',
            'rut' => '12345678-9',
            'tipo_cliente' => 'Cliente Público',
            'vendedores_a_cargo' => ['Juan Pérez', 'María Silva'],
            'informacion_adicional' => 'Hospital público de alta complejidad. Requiere equipamiento para UCI y urgencias.',
            'email' => 'compras@hospitalregional.cl',
            'telefono' => '+56 2 2234 5678',
            'direccion' => 'Av. Libertador Bernardo O\'Higgins 1234, Santiago, Región Metropolitana'
        ]);

        // Cliente 2: Clínica Privada
        $cliente2 = Cliente::create([
            'nombre_institucion' => 'Clínica Las Condes',
            'nombre_contacto' => 'Ing. Patricia Rojas',
            'rut' => '87654321-0',
            'tipo_cliente' => 'Cliente Privado',
            'vendedores_a_cargo' => ['Ana García'],
            'informacion_adicional' => 'Clínica privada de alto estándar. Enfoque en tecnología de punta.',
            'email' => 'adquisiciones@clinicalascondes.cl',
            'telefono' => '+56 2 2610 8000',
            'direccion' => 'Estoril 450, Las Condes, Santiago'
        ]);

        $this->crearClientesComunes($cliente1, $cliente2);
    }

    private function crearClientesAlternativos()
    {
        echo "📝 Creando clientes con emails alternativos...\n";

        // Cliente 1: Hospital Regional (email alternativo)
        $cliente1 = Cliente::firstOrCreate(
            ['rut' => '12345678-9'], // Buscar por RUT
            [
                'nombre_institucion' => 'Hospital Regional de Santiago',
                'nombre_contacto' => 'Dr. Carlos Mendoza',
                'tipo_cliente' => 'Cliente Público',
                'vendedores_a_cargo' => ['Juan Pérez', 'María Silva'],
                'informacion_adicional' => 'Hospital público de alta complejidad. Requiere equipamiento para UCI y urgencias.',
                'email' => 'licitaciones@hospitalregional.cl', // Email alternativo
                'telefono' => '+56 2 2234 5678',
                'direccion' => 'Av. Libertador Bernardo O\'Higgins 1234, Santiago, Región Metropolitana'
            ]
        );

        // Cliente 2: Clínica Privada (email alternativo)
        $cliente2 = Cliente::firstOrCreate(
            ['rut' => '87654321-0'],
            [
                'nombre_institucion' => 'Clínica Las Condes',
                'nombre_contacto' => 'Ing. Patricia Rojas',
                'tipo_cliente' => 'Cliente Privado',
                'vendedores_a_cargo' => ['Ana García'],
                'informacion_adicional' => 'Clínica privada de alto estándar. Enfoque en tecnología de punta.',
                'email' => 'gerencia@clinicalascondes.cl', // Email alternativo
                'telefono' => '+56 2 2610 8000',
                'direccion' => 'Estoril 450, Las Condes, Santiago'
            ]
        );

        $this->crearClientesComunes($cliente1, $cliente2);
    }

    private function crearClientesComunes($cliente1, $cliente2)
    {
        // Cliente 3: Centro Médico
        $cliente3 = Cliente::firstOrCreate(
            ['rut' => '11223344-5'],
            [
                'nombre_institucion' => 'Centro Médico San Juan',
                'nombre_contacto' => 'Dra. Luisa Fernández',
                'tipo_cliente' => 'Cliente Privado',
                'vendedores_a_cargo' => ['Roberto López'],
                'informacion_adicional' => 'Centro médico familiar. Especialización en medicina general y pediatría.',
                'email' => 'gerencia@centromedicosanjuan.cl',
                'telefono' => '+56 2 2987 6543',
                'direccion' => 'San Juan 789, Ñuñoa, Santiago'
            ]
        );

        // Cliente 4: Revendedor
        $cliente4 = Cliente::firstOrCreate(
            ['rut' => '55667788-9'],
            [
                'nombre_institucion' => 'MedEquip Distribuciones Ltda.',
                'nombre_contacto' => 'Sr. Manuel Torres',
                'tipo_cliente' => 'Revendedor',
                'vendedores_a_cargo' => ['Juan Pérez', 'Ana García'],
                'informacion_adicional' => 'Distribuidor autorizado para la región sur. Maneja grandes volúmenes.',
                'email' => 'ventas@medequip.cl',
                'telefono' => '+56 41 233 4455',
                'direccion' => 'Av. Colón 567, Concepción, Región del Biobío'
            ]
        );

        // Cliente 5: Laboratorio
        $cliente5 = Cliente::firstOrCreate(
            ['rut' => '99887766-3'],
            [
                'nombre_institucion' => 'Laboratorio Clínico BioMed',
                'nombre_contacto' => 'QF. Andrea Morales',
                'tipo_cliente' => 'Cliente Privado',
                'vendedores_a_cargo' => ['María Silva'],
                'informacion_adicional' => 'Laboratorio especializado en análisis clínicos. Requiere equipos de alta precisión.',
                'email' => 'compras@biomed.cl',
                'telefono' => '+56 2 2456 7890',
                'direccion' => 'Providencia 234, Providencia, Santiago'
            ]
        );

        // Crear contactos adicionales (solo si no existen)
        $this->crearContactosAdicionales($cliente1, $cliente2, $cliente4);

        echo "✅ Se han creado/verificado " . Cliente::count() . " clientes en total\n";
        echo "✅ Se han creado " . Contacto::count() . " contactos adicionales\n";
        echo "📋 Estructura compatible con tus migraciones existentes\n";
    }

    private function crearContactosAdicionales($cliente1, $cliente2, $cliente4)
    {
        // Contacto 1: UCI Hospital
        Contacto::firstOrCreate(
            [
                'cliente_id' => $cliente1->id,
                'email' => 'carmen.lopez@hospitalregional.cl'
            ],
            [
                'nombre' => 'Enfermera Jefe Carmen López',
                'cargo' => 'Jefe de Enfermería UCI',
                'telefono' => '+56 2 2234 5679',
                'area' => 'UCI',
                'notas' => 'Contacto para temas de equipamiento de UCI'
            ]
        );

        // Contacto 2: Mantenimiento Clínica
        Contacto::firstOrCreate(
            [
                'cliente_id' => $cliente2->id,
                'email' => 'miguel.sanchez@clinicalascondes.cl'
            ],
            [
                'nombre' => 'Ing. Miguel Sánchez',
                'cargo' => 'Jefe de Mantenimiento',
                'telefono' => '+56 2 2610 8001',
                'area' => 'Mantenimiento',
                'notas' => 'Contacto para temas técnicos y mantenimiento'
            ]
        );

        // Contacto 3: Comercial Revendedor
        Contacto::firstOrCreate(
            [
                'cliente_id' => $cliente4->id,
                'email' => 'elena.vargas@medequip.cl'
            ],
            [
                'nombre' => 'Sra. Elena Vargas',
                'cargo' => 'Gerente Comercial',
                'telefono' => '+56 41 233 4456',
                'area' => 'Ventas',
                'notas' => 'Contacto para negociaciones comerciales'
            ]
        );

        // Contacto 4: Administración Hospital
        Contacto::firstOrCreate(
            [
                'cliente_id' => $cliente1->id,
                'email' => 'jose.ruiz@hospitalregional.cl'
            ],
            [
                'nombre' => 'Administrador José Ruiz',
                'cargo' => 'Jefe de Administración',
                'telefono' => '+56 2 2234 5680',
                'area' => 'Administración',
                'notas' => 'Contacto para temas de facturación y pagos'
            ]
        );
    }
}