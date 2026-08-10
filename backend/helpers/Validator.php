<?php

/**
 * Clase Validator
 * Reglas de validación reutilizables para los datos de entrada
 * del módulo de eventos.
 */
class Validator
{
    /**
     * Valida los datos para la creación de un evento.
     *
     * @param array $data Datos crudos recibidos (JSON decodificado)
     * @return array Lista de errores. Vacío si no hay errores.
     */
    public static function validarCrearEvento(array $data): array
    {
        $errores = [];

        // --- Título ---
        if (empty($data['titulo']) || !is_string($data['titulo'])) {
            $errores['titulo'] = 'El título es obligatorio.';
        } elseif (mb_strlen(trim($data['titulo'])) < 5) {
            $errores['titulo'] = 'El título debe tener al menos 5 caracteres.';
        } elseif (mb_strlen($data['titulo']) > 200) {
            $errores['titulo'] = 'El título no puede exceder 200 caracteres.';
        }

        // --- Descripción ---
        if (empty($data['descripcion']) || !is_string($data['descripcion'])) {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        } elseif (mb_strlen(trim($data['descripcion'])) < 10) {
            $errores['descripcion'] = 'La descripción debe tener al menos 10 caracteres.';
        }

        // --- Fecha del evento ---
        if (empty($data['fecha_evento'])) {
            $errores['fecha_evento'] = 'La fecha del evento es obligatoria.';
        } else {
            $fecha = DateTime::createFromFormat('Y-m-d', $data['fecha_evento']);
            $esFechaValida = $fecha && $fecha->format('Y-m-d') === $data['fecha_evento'];

            if (!$esFechaValida) {
                $errores['fecha_evento'] = 'El formato de fecha debe ser YYYY-MM-DD.';
            } else {
                $hoy = new DateTime('today');
                if ($fecha < $hoy) {
                    $errores['fecha_evento'] = 'La fecha del evento no puede ser en el pasado.';
                }
            }
        }

        // --- Hora (opcional) ---
        if (!empty($data['hora_evento'])) {
            $hora = DateTime::createFromFormat('H:i', $data['hora_evento'])
                ?: DateTime::createFromFormat('H:i:s', $data['hora_evento']);
            if (!$hora) {
                $errores['hora_evento'] = 'El formato de hora debe ser HH:MM.';
            }
        }

        // --- Categoría ---
        if (empty($data['categoria_id']) || !filter_var($data['categoria_id'], FILTER_VALIDATE_INT)) {
            $errores['categoria_id'] = 'Debe seleccionar una categoría válida.';
        }

        // --- Aforo máximo ---
        if (!isset($data['aforo_maximo']) || !filter_var($data['aforo_maximo'], FILTER_VALIDATE_INT)) {
            $errores['aforo_maximo'] = 'El límite de aforo es obligatorio y debe ser un número entero.';
        } elseif ((int) $data['aforo_maximo'] <= 0) {
            $errores['aforo_maximo'] = 'El límite de aforo debe ser mayor a 0.';
        } elseif ((int) $data['aforo_maximo'] > 100000) {
            $errores['aforo_maximo'] = 'El límite de aforo ingresado no es razonable.';
        }

        // --- Lugar (opcional pero recomendado) ---
        if (!empty($data['lugar']) && mb_strlen($data['lugar']) > 200) {
            $errores['lugar'] = 'El lugar no puede exceder 200 caracteres.';
        }

        return $errores;
    }
}
