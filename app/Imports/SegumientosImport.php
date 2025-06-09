<?php

namespace App\Imports;

use App\Models\Seguimiento;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Cotizacion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeguimientosImport implements ToModel, WithHeadingRow, WithValidation
{
    private $filasProcesadas = 0;
    private $errores = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->filasProcesadas++;

        try {
            // Buscar cliente por nombre o RUT
            $cliente = $this->buscarCliente($row['cliente'] ?? '');
            if (!$cliente) {
                $this->errores[] = "Fila {$this->filasProcesadas}: Cliente no encontrado - " . ($row['cliente'] ?? 'N/A');
                return null;
            }

            // Buscar vendedor por nombre o email
            $vendedor = $this->buscarVendedor($row['vendedor'] ?? '');
            if (!$vendedor) {
                $this->errores[] = "Fila {$this->filasProcesadas}: Vendedor no encontrado - " . ($row['vendedor'] ?? 'N/A');
                return null;
            }

            // Buscar cotización si se proporciona
            $cotizacion = null;
            if (!empty($row['cotizacion'])) {
                $cotizacion = Cotizacion::find($row['cotizacion']);
            }

            // Procesar fecha de próxima gestión
            $proximaGestion = $this->procesarFecha($row['proxima_gestion'] ?? '');
            if (!$proximaGestion) {
                $this->errores[] = "Fila {$this->filasProcesadas}: Fecha de próxima gestión inválida - " . ($row['proxima_gestion'] ?? 'N/A');
                return null;
            }

            // Validar estado
            $estado = $this->validarEstado($row['estado'] ?? 'pendiente');
            
            // Validar prioridad
            $prioridad = $this->validarPrioridad($row['prioridad'] ?? 'media');

            // Procesar fecha de última gestión
            $ultimaGestion = null;
            if (!empty($row['ultima_gestion'])) {
                $ultimaGestion = $this->procesarFecha($row['ultima_gestion']);
            }

            return new Seguimiento([
                'cliente_id' => $cliente->id,
                'cotizacion_id' => $cotizacion ? $cotizacion->id : null,
                'vendedor_id' => $vendedor->id,
                'estado' => $estado,
                'prioridad' => $prioridad,
                'ultima_gestion' => $ultimaGestion,
                'proxima_gestion' => $proximaGestion,
                'notas' => $row['notas'] ?? '',
                'resultado_ultima_gestion' => $row['resultado_ultima_gestion'] ?? '',
            ]);

        } catch (\Exception $e) {
            Log::error("Error procesando fila {$this->filasProcesadas}: " . $e->getMessage());
            $this->errores[] = "Fila {$this->filasProcesadas}: Error inesperado - " . $e->getMessage();
            return null;
        }
    }

    /**
     * Reglas de validación para cada fila
     */
    public function rules(): array
    {
        return [
            'cliente' => 'required|string',
            'vendedor' => 'required|string',
            'proxima_gestion' => 'required',
            'estado' => 'nullable|in:pendiente,en_proceso,completado,vencido,reprogramado',
            'prioridad' => 'nullable|in:baja,media,alta,urgente',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function customValidationMessages()
    {
        return [
            'cliente.required' => 'El campo cliente es obligatorio.',
            'vendedor.required' => 'El campo vendedor es obligatorio.',
            'proxima_gestion.required' => 'La fecha de próxima gestión es obligatoria.',
            'estado.in' => 'El estado debe ser: pendiente, en_proceso, completado, vencido o reprogramado.',
            'prioridad.in' => 'La prioridad debe ser: baja, media, alta o urgente.',
        ];
    }

    /**
     * Buscar cliente por nombre o RUT
     */
    private function buscarCliente($termino)
    {
        if (empty($termino)) {
            return null;
        }

        // Limpiar el término de búsqueda
        $termino = trim($termino);

        // Buscar por nombre exacto primero
        $cliente = Cliente::where('nombre', 'LIKE', $termino)->first();
        
        if (!$cliente) {
            // Buscar por nombre parcial
            $cliente = Cliente::where('nombre', 'LIKE', "%{$termino}%")->first();
        }

        if (!$cliente) {
            // Buscar por RUT (limpiar puntos y guiones)
            $rutLimpio = preg_replace('/[^0-9kK]/', '', $termino);
            $cliente = Cliente::where('rut', 'LIKE', "%{$rutLimpio}%")->first();
        }

        return $cliente;
    }

    /**
     * Buscar vendedor por nombre o email
     */
    private function buscarVendedor($termino)
    {
        if (empty($termino)) {
            return null;
        }

        $termino = trim($termino);

        // Buscar por nombre exacto
        $vendedor = User::where('name', 'LIKE', $termino)->first();
        
        if (!$vendedor) {
            // Buscar por nombre parcial
            $vendedor = User::where('name', 'LIKE', "%{$termino}%")->first();
        }

        if (!$vendedor) {
            // Buscar por email
            $vendedor = User::where('email', 'LIKE', "%{$termino}%")->first();
        }

        return $vendedor;
    }

    /**
     * Procesar diferentes formatos de fecha
     */
    private function procesarFecha($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        try {
            // Si es un número (fecha de Excel)
            if (is_numeric($fecha)) {
                return Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha)->format('Y-m-d'));
            }

            // Intentar diferentes formatos de fecha
            $formatos = [
                'Y-m-d',
                'd/m/Y',
                'm/d/Y',
                'd-m-Y',
                'm-d-Y',
                'Y/m/d',
                'd.m.Y',
                'Y.m.d',
            ];

            foreach ($formatos as $formato) {
                try {
                    return Carbon::createFromFormat($formato, $fecha);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Último intento con Carbon::parse
            return Carbon::parse($fecha);

        } catch (\Exception $e) {
            Log::warning("No se pudo procesar la fecha: {$fecha}");
            return null;
        }
    }

    /**
     * Validar y normalizar estado
     */
    private function validarEstado($estado)
    {
        $estadosValidos = ['pendiente', 'en_proceso', 'completado', 'vencido', 'reprogramado'];
        $estado = strtolower(trim($estado));
        
        // Mapeo de estados alternativos
        $mapeo = [
            'pendiente' => 'pendiente',
            'en proceso' => 'en_proceso',
            'en_proceso' => 'en_proceso',
            'proceso' => 'en_proceso',
            'completado' => 'completado',
            'completo' => 'completado',
            'terminado' => 'completado',
            'vencido' => 'vencido',
            'atrasado' => 'vencido',
            'reprogramado' => 'reprogramado',
            'reagendado' => 'reprogramado',
        ];

        return $mapeo[$estado] ?? 'pendiente';
    }

    /**
     * Validar y normalizar prioridad
     */
    private function validarPrioridad($prioridad)
    {
        $prioridadesValidas = ['baja', 'media', 'alta', 'urgente'];
        $prioridad = strtolower(trim($prioridad));
        
        // Mapeo de prioridades alternativas
        $mapeo = [
            'baja' => 'baja',
            'bajo' => 'baja',
            'low' => 'baja',
            'media' => 'media',
            'medio' => 'media',
            'normal' => 'media',
            'medium' => 'media',
            'alta' => 'alta',
            'alto' => 'alta',
            'high' => 'alta',
            'urgente' => 'urgente',
            'critico' => 'urgente',
            'crítico' => 'urgente',
            'emergency' => 'urgente',
        ];

        return $mapeo[$prioridad] ?? 'media';
    }

    /**
     * Obtener errores de procesamiento
     */
    public function getErrores()
    {
        return $this->errores;
    }

    /**
     * Obtener cantidad de filas procesadas
     */
    public function getFilasProcesadas()
    {
        return $this->filasProcesadas;
    }
}