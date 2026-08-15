<?php

class Validator
{
    public static function validarCrearEvento(array $data): array
    {
        $errores = [];

        if (empty($data['titulo']) || !is_string($data['titulo'])) {
            $errores['titulo'] = 'El título es obligatorio.';
        } elseif (mb_strlen(trim($data['titulo'])) < 5) {
            $errores['titulo'] = 'El título debe tener al menos 5 caracteres.';
        } elseif (mb_strlen($data['titulo']) > 200) {
            $errores['titulo'] = 'El título no puede exceder 200 caracteres.';
        }

        if (empty($data['descripcion']) || !is_string($data['descripcion'])) {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        } elseif (mb_strlen(trim($data['descripcion'])) < 10) {
            $errores['descripcion'] = 'La descripción debe tener al menos 10 caracteres.';
        }

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

        if (!empty($data['hora_evento'])) {
            $hora = DateTime::createFromFormat('H:i', $data['hora_evento'])
                ?: DateTime::createFromFormat('H:i:s', $data['hora_evento']);
            if (!$hora) {
                $errores['hora_evento'] = 'El formato de hora debe ser HH:MM.';
            }
        }

        if (empty($data['categoria_id']) || !filter_var($data['categoria_id'], FILTER_VALIDATE_INT)) {
            $errores['categoria_id'] = 'Debe seleccionar una categoría válida.';
        }

        if (!isset($data['aforo_maximo']) || !filter_var($data['aforo_maximo'], FILTER_VALIDATE_INT)) {
            $errores['aforo_maximo'] = 'El límite de aforo es obligatorio y debe ser un número entero.';
        } elseif ((int) $data['aforo_maximo'] <= 0) {
            $errores['aforo_maximo'] = 'El límite de aforo debe ser mayor a 0.';
        } elseif ((int) $data['aforo_maximo'] > 100000) {
            $errores['aforo_maximo'] = 'El límite de aforo ingresado no es razonable.';
        }

        if (!empty($data['lugar']) && mb_strlen($data['lugar']) > 200) {
            $errores['lugar'] = 'El lugar no puede exceder 200 caracteres.';
        }

        return $errores;
    }

    public static function validarCrearResena(array $data): array
    {
        $errores = [];

        if (empty($data['evento_id']) || !filter_var($data['evento_id'], FILTER_VALIDATE_INT)) {
            $errores['evento_id'] = 'El ID del evento es inválido u obligatorio.';
        }

        if (!isset($data['calificacion']) || !filter_var($data['calificacion'], FILTER_VALIDATE_INT)) {
            $errores['calificacion'] = 'La calificación es obligatoria.';
        } elseif ((int) $data['calificacion'] < 1 || (int) $data['calificacion'] > 5) {
            $errores['calificacion'] = 'La calificación debe estar entre 1 y 5 estrellas.';
        }

        if (!empty($data['comentario']) && mb_strlen($data['comentario']) > 500) {
            $errores['comentario'] = 'El comentario no puede exceder los 500 caracteres.';
        }

        return $errores;
    }

    public static function validarRegistro(array $data): array
        {
            $errores = [];

            if (empty($data['correo']) || !is_string($data['correo'])) {
                $errores['correo'] = 'El correo es obligatorio.';
            } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'El correo no tiene un formato válido.';
            } elseif (!str_ends_with(strtolower($data['correo']), '@espol.edu.ec')) {
                $errores['correo'] = 'Debe utilizar un correo institucional @espol.edu.ec.';
            }

            if (empty($data['contrasena']) || !is_string($data['contrasena'])) {
                $errores['contrasena'] = 'La contraseña es obligatoria.';
            } elseif (strlen($data['contrasena']) < 8) {
                $errores['contrasena'] = 'La contraseña debe tener al menos 8 caracteres.';
            }

            $cargosPermitidos = [
                'estudiante',
                'administrativo',
                'profesor'
            ];

            if (empty($data['cargo'])) {
                $errores['cargo'] = 'El cargo es obligatorio.';
            } elseif (!in_array($data['cargo'], $cargosPermitidos, true)) {
                $errores['cargo'] = 'El cargo seleccionado no es válido.';
            }

            return $errores;
        }

        public static function validarLogin(array $data): array
        {
            $errores = [];

            if (empty($data['correo']) || !is_string($data['correo'])) {
                $errores['correo'] = 'El correo es obligatorio.';
            } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'El correo no tiene un formato válido.';
            }

            if (empty($data['contrasena']) || !is_string($data['contrasena'])) {
                $errores['contrasena'] = 'La contraseña es obligatoria.';
            }

            return $errores;
        }

}

