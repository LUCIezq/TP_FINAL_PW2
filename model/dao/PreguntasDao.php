<?php

class PreguntasDao
{
    private MyConexion $conexion;
    private CategoryDao $categoryDao;
    private EstadoPreguntaDao $estadoPreguntaDao;
    private NivelDao $nivelDao;

    public function __construct(MyConexion $conexion, CategoryDao $categoryDao, EstadoPreguntaDao $estadoPreguntaDao, NivelDao $nivelDao)
    {
        $this->conexion = $conexion;
        $this->categoryDao = $categoryDao;
        $this->estadoPreguntaDao = $estadoPreguntaDao;
        $this->nivelDao = $nivelDao;
    }

    public function getPreguntasPorCategoria($idCategoria)
    {
        $sql = "SELECT * FROM pregunta WHERE genero_id = ?";
        $params = [$idCategoria];
        $types = "i";

        $result = $this->conexion->executePrepared($sql, $types, $params);

        return $this->conexion->processData($result);
    }

    public function obtenerPreguntaParaPartidaEnCurso($genero_id, $nivelUsuarioId, $preguntasUsadas)
    {
        $placeholders = count($preguntasUsadas) ? rtrim(str_repeat('?,', count($preguntasUsadas)), ',') : null;

        $sql = "SELECT p.id, p.genero_id, p.texto, p.nivel_id
            FROM pregunta p
            WHERE p.genero_id = ? 
            AND p.nivel_id = ? 
            AND p.estado_id = 1"
            . ($placeholders ? " AND p.id NOT IN ($placeholders)" : "")
            . " ORDER BY RAND() LIMIT 1";

        $params = [$genero_id, $nivelUsuarioId, ...$preguntasUsadas];
        $types = 'ii' . str_repeat('i', count($preguntasUsadas));

        $data = $this->conexion->processData($this->conexion->executePrepared($sql, $types, $params));

        $nivelTemp = $nivelUsuarioId;

        while (empty($data[0]) && $nivelTemp > 1) {
            $nivelTemp--;
            $params = [$genero_id, $nivelTemp, ...$preguntasUsadas];
            $types = 'ii' . str_repeat('i', count($preguntasUsadas));
            $data = $this->conexion->processData($this->conexion->executePrepared($sql, $types, $params));
        }

        if (empty($data[0])) {
            return null;
        }

        $data[0]['respuestas'] = $this->obtenerRespuestasPorIdPregunta($data[0]['id']);

        return $data[0];
    }


    public function createQuestion($data)
    {

        $genero_id = $data["categoriaId"];
        $texto = $data["pregunta"];
        $usuario_id = $data["usuarioId"];
        $estado_id = $this->estadoPreguntaDao->obtenerIdDeEstadoPorNombre(EstadoPreguntaNombre::SUGERIDA->value);
        $respuestas = $data['respuestas'];

        $sql = "INSERT INTO pregunta (genero_id,texto,usuario_id,estado_id,nivel_id) VALUES (?,?,?,?,?)";

        $params = [
            $genero_id,
            $texto,
            $usuario_id,
            $estado_id,
            1
        ];

        $types = "isiii";

        $idPregunta = $this->conexion->executePrepared($sql, $types, $params);

        $result = null;

        foreach ($respuestas as $index => $text) {
            $isCorrect = ($index === $data["indiceCorrecta"]) ? 1 : 0;

            $result = $this->createAnswer($text, $isCorrect, $idPregunta);
        }
        return $idPregunta > 0 && $result != null;
    }
    public function obtenerRespuestaCorrecta($preguntaId)
    {
        $sql = "SELECT *
                FROM respuesta 
                WHERE pregunta_id = ? 
                AND es_correcta = 1";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "i", [$preguntaId])
        );

        return $data[0];
    }
    public function createAnswer($text, $isCorrect, $questionId)
    {
        $sql = "INSERT into respuesta (texto,es_correcta,pregunta_id)
        VALUES(?,?,?)";

        $params = [
            $text,
            $isCorrect,
            $questionId
        ];

        $types = "sii";

        return $this->conexion->executePrepared($sql, $types, $params);
    }
    public function obtenerPreguntasSugeridas()
    {
        $sql = " SELECT 
            u.nombre_usuario AS usuario,
            p.texto AS enunciado,
            p.id AS pregunta_id,
            r.texto as respuesta,
            r.es_correcta,
            g.nombre as genero_nombre,
            p.estado_id,
            r.id AS respuesta_id
                FROM pregunta p
                JOIN usuario u ON p.usuario_id = u.id
                LEFT JOIN respuesta r ON r.pregunta_id = p.id
                join genero g on g.id = p.genero_id
            WHERE p.estado_id = ?";

        $params = [$this->estadoPreguntaDao->obtenerIdDeEstadoPorNombre(EstadoPreguntaNombre::SUGERIDA->value)];
        $types = "i";

        $result = $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        );
        $questions = [];

        foreach ($result as $row) {

            $id = $row['pregunta_id'];

            if (!isset($questions[$id])) {
                $questions[$id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'enunciado' => $row['enunciado'],
                    'genero_nombre' => $row['genero_nombre'],
                    'usuario' => $row['usuario'],
                    'activa' => (int) $row['estado_id'] === $this->estadoPreguntaDao->obtenerIdDeEstadoPorNombre(EstadoPreguntaNombre::ACTIVA->value) ? true : false,
                    'respuestas' => [],
                ];
            }

            if (!empty($row['respuesta_id'])) {
                $questions[$id]['respuestas'][] = [
                    'respuesta_id' => $row['respuesta_id'],
                    'respuesta' => $row['respuesta'],
                    'es_correcta' => $row['es_correcta']
                ];
            }
        }

        return array_values($questions);
    }

    public function obtenerPreguntaPorIdParaPartida($idPregunta)
    {
        $sql = 'SELECT * FROM pregunta WHERE id = ?';
        $params = [$idPregunta];
        $types = 'i';

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        )[0];

        $data['respuestas'] = $this->obtenerRespuestasPorIdPregunta($data['id']);

        return $data;
    }
    public function obtenerPreguntaPorId($id)
    {
        $sql = "SELECT 
                p.id AS pregunta_id,
                p.texto AS pregunta_texto,
                ep.nombre as estado_nombre,
                g.id AS genero_id,
                g.nombre AS genero_nombre,
                r.id AS respuesta_id,
                r.texto AS respuesta_texto,
                r.es_correcta AS correcta,
                ep.id as estado_id
                
            FROM pregunta p
            JOIN genero g ON g.id = p.genero_id
            JOIN estado_pregunta ep on ep.id = p.estado_id
            JOIN respuesta r ON r.pregunta_id = p.id
            WHERE p.id = ?";

        $params = [$id];
        $types = "i";

        $rows = $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        );

        if (empty($rows)) {
            return null;
        }

        $pregunta = [
            'id' => $rows[0]['pregunta_id'],
            'texto' => $rows[0]['pregunta_texto'],
            'genero_id' => $rows[0]['genero_id'],
            'genero_nombre' => $rows[0]['genero_nombre'],
            'estado_id' => $rows[0]['estado_id'],
            'respuestas' => []
        ];

        foreach ($rows as $row) {
            $pregunta['respuestas'][] = [
                'id' => $row['respuesta_id'],
                'texto' => $row['respuesta_texto'],
                'correcta' => $row['correcta']
            ];
        }

        return $pregunta;
    }
    public function getQuestionById($id)
    {

        $sql = "SELECT p.id as pregunta_id,
        p.texto as pregunta,
        p.genero_id,
        r.texto as respuesta
        FROM pregunta p 
        LEFT JOIN respuesta r on r.pregunta_id = p.id
        WHERE p.id = ?";
        $params = [$id];
        $types = "i";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        );

        $questions = [];

        foreach ($data as $row) {
            $id = $row['pregunta_id'];

            if (!isset($questions[$id])) {
                $questions[$id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'pregunta' => $row['pregunta'],
                    'genero_id' => $row['genero_id'],
                    'respuestas' => [
                        $row['respuesta']

                    ],
                ];
            } else {
                $questions[$id]['respuestas'][] = $row['respuesta'];
            }
        }
        return array_values($questions)[0] ?? null;
    }
    public function aprobarPregunta($preguntas)
    {
        $cantidad = count($preguntas);

        if ($cantidad === 0)
            return false;

        $placeholders = implode(',', array_fill(0, $cantidad, '?'));

        $types = 'i' . str_repeat('i', $cantidad);

        $sql = "
        UPDATE pregunta
        SET estado_id = (
            SELECT id FROM estado_pregunta 
            WHERE nombre = ?
        )
        WHERE id IN ($placeholders)
    ";

        $params = [
            $this->estadoPreguntaDao->obtenerIdDeEstadoPorNombre(
                EstadoPreguntaNombre::ACTIVA->value
            )
        ];

        foreach ($preguntas as $p) {
            $params[] = $p;
        }

        return $this->conexion->executePrepared($sql, $types, $params) > 0;
    }
    public function actualizarPregunta($data)
    {
        $cambios = [];
        $params = [];
        $types = '';
        $hayCambios = false;

        $preguntaForm_id = $data['id'];
        $textoForm = $data['texto'];
        $genero_idForm = $data['genero_id'];
        $id_correctaForm = $data['correcta'];
        $respuestasForm = $data['respuestas'];
        $estadoIdForm = $data['estado_id'];

        $preguntaEnBd = $this->obtenerPreguntaPorId($preguntaForm_id);

        if (!$preguntaEnBd) {
            return 'Pregunta invalida.';
        }

        if ($preguntaEnBd['texto'] !== $textoForm) {
            $cambios[] = 'texto = ?';
            $params[] = $textoForm;
            $types .= 's';
        }

        $generos = $this->categoryDao->getAll();
        if (!in_array($genero_idForm, array_column($generos, 'id'))) {
            return 'Genero invalido.';
        }

        if ($preguntaEnBd['genero_id'] !== (int) $genero_idForm) {
            $cambios[] = 'genero_id = ?';
            $params[] = $genero_idForm;
            $types .= 'i';
        }

        $respuestaCorrectaBd = $this->obtenerRespuestaCorrectaPorIdPregunta($preguntaEnBd['id']);

        if ($respuestaCorrectaBd['id'] != $id_correctaForm) {
            $this->actualizarRespuestaCorrecta($id_correctaForm, $preguntaEnBd['id']);
            $hayCambios = true;
        }

        $respuestasBd = $this->obtenerRespuestasPorIdPregunta($preguntaEnBd['id']);

        foreach ($respuestasBd as $respuestaBd) {
            $rid = $respuestaBd['id'];

            if (
                isset($respuestasForm[$rid]) &&
                $respuestasForm[$rid]['texto'] !== $respuestaBd['texto']
            ) {

                $this->actualizarEnunciadoRespuesta($rid, $respuestasForm[$rid]['texto']);
                $hayCambios = true;
            }
        }

        if ($preguntaEnBd['estado_id'] !== (int) $estadoIdForm) {
            $cambios[] = 'estado_id = ?';
            $params[] = $estadoIdForm;
            $types .= 'i';
        }
        if (empty($cambios) && !$hayCambios) {
            return 'No hay cambios para actualizar.';
        }

        if (count($cambios) > 0) {
            $sql = 'UPDATE pregunta SET ' . implode(',', $cambios) . ' WHERE id = ?';

            $params[] = $preguntaForm_id;
            $types .= 'i';

            $this->conexion->executePrepared($sql, $types, $params);
        }

        return "Pregunta actualizada correctamente.";
    }
    public function inactivarPregunta($id)
    {
        $sql = 'UPDATE pregunta set estado_id=2 where id = ?';
        $params = [$id];
        $types = 'i';
        return $this->conexion->executePrepared($sql, $types, $params) === 1;
    }

    public function actualizarEnunciadoRespuesta($respuestaId, $nuevoTexto)
    {
        $sql = 'UPDATE respuesta set texto = ? where id = ?';
        $paramsUpdate = [
            $nuevoTexto,
            $respuestaId
        ];
        $typesUpdate = 'si';
        $this->conexion->executePrepared($sql, $typesUpdate, $paramsUpdate);
    }
    public function actualizarRespuestaCorrecta($idCorrecta, $idPregunta)
    {
        $sql = 'UPDATE respuesta
            SET es_correcta = CASE 
            WHEN id = ? THEN 1 
            ELSE 0 
            END 
            WHERE pregunta_id = ?';
        $params = [
            $idCorrecta,
            $idPregunta
        ];
        $types = 'ii';
        $this->conexion->executePrepared($sql, $types, $params);
    }
    public function obtenerRespuestasPorIdPregunta($preguntaId)
    {
        $sql = "SELECT id,texto,es_correcta
        from respuesta 
        where pregunta_id = ?
        ORDER BY RAND()";

        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, 'i', [$preguntaId])
        );
    }

    public function obtenerRespuestasPorIdRespuesta($idRespuesta)
    {
        $sql = "SELECT *
        from respuesta 
        where id = ?";

        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, 'i', [$idRespuesta])
        )[0];
    }

    public function obtenerRespuestaCorrectaPorIdPregunta($preguntaId)
    {
        $sql = "SELECT id,texto 
        from respuesta 
        where pregunta_id = ? AND es_correcta = 1";

        $params = [$preguntaId];
        $types = "i";
        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        )[0] ?? null;
    }
    public function eliminarPreguntasPorIds($ids)
    {
        if (empty($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count(value: $ids), '?'));

        $sql = "DELETE FROM pregunta WHERE id IN ($placeholders)";

        $types = str_repeat('i', count($ids));

        return $this->conexion->executePrepared($sql, $types, $ids) > 0;
    }
    public function getAllSystemQuestions()
    {
        $sql = "SELECT 
        p.id as pregunta_id,
        p.texto as enunciado,
        u.nombre_usuario as usuario,
        p.genero_id,
        r.texto as respuesta,
        r.id as respuesta_id,
        r.es_correcta,
        g.nombre as genero_nombre,
        p.estado_id
        
        FROM pregunta p 
        
        JOIN usuario u ON p.usuario_id = u.id
        JOIN genero g ON g.id = p.genero_id
        LEFT JOIN respuesta r on r.pregunta_id = p.id";

        $data = $this->conexion->query($sql);

        $questions = [];

        foreach ($data as $row) {

            $id = $row['pregunta_id'];

            if (!isset($questions[$id])) {
                $questions[$id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'enunciado' => $row['enunciado'],
                    'genero_id' => $row['genero_id'],
                    'genero_nombre' => $row['genero_nombre'],
                    'usuario' => $row['usuario'],
                    'activa' => (int) $row['estado_id'] === $this->estadoPreguntaDao->obtenerIdDeEstadoPorNombre(EstadoPreguntaNombre::ACTIVA->value) ? true : false,
                    'respuestas' => [],
                ];
            }

            if (!empty($row['respuesta_id'])) {
                $questions[$id]['respuestas'][] = [
                    'respuesta' => $row['respuesta'],
                    'respuesta_id' => $row['respuesta_id'],
                    'es_correcta' => $row['es_correcta']
                ];
            }
        }

        return array_values($questions);
    }
    public function verificarRespuesta($respuestaId, $preguntaId)
    {
        $sql = "SELECT es_correcta 
                FROM respuesta 
                WHERE id = ? 
                AND pregunta_id = ?";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared($sql, "ii", [$respuestaId, $preguntaId])
        );

        return $data[0]['es_correcta'] ?? null;
    }
    public function obtenerPreguntaInicial($generoId, $nivelUsuarioId)
    {

        $sql = "SELECT id,genero_id,texto 
            FROM pregunta 
            WHERE genero_id = ? 
            AND nivel_id = ?
            AND estado_id = 1
            ORDER BY RAND() LIMIT 1";

        $data = $this->conexion->processData(
            $this->conexion->executePrepared(
                $sql,
                "ii",
                [$generoId, $nivelUsuarioId]
            )
        );

        if (empty($data[0])) {
            while ($nivelUsuarioId > 1) {
                $nivelUsuarioId--;
                $data = $this->conexion->processData(
                    $this->conexion->executePrepared($sql, 'ii', [$generoId, $nivelUsuarioId])
                );
                if ($data)
                    break;
            }
        }

        $data[0]['respuestas'] = $this->obtenerRespuestasPorIdPregunta($data[0]['id']);

        return $data[0];
    }
    public function obtenerVecesMostradaUnaPregunta($idPregunta)
    {
        $sql = 'SELECT COUNT(*) as veces_mostrada
                FROM historial_partida
                where pregunta_id = ?';

        $types = 'i';
        $params = [$idPregunta];

        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        )[0]['veces_mostrada'];
    }
    public function obtenerVecesCorrectaUnaPregunta($idPregunta)
    {
        $sql = 'SELECT COUNT(*) as veces_correcta
                FROM historial_partida
                where pregunta_id = ?
                and respondida_correctamente = ?';

        $types = 'ii';
        $params = [$idPregunta, 1];

        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        )[0]['veces_correcta'];
    }
    public function calcularRatioDePregunta($idPregunta)
    {
        $vecesCorrecta = $this->obtenerVecesCorrectaUnaPregunta($idPregunta);
        $vecesMostrada = $this->obtenerVecesMostradaUnaPregunta($idPregunta);

        if ($vecesMostrada === 0) {
            return 0;
        }
        return 1 - $vecesCorrecta / $vecesMostrada;
    }
    public function actualizarDificultadDePregunta($idPregunta)
    {
        $ratio = $this->calcularRatioDePregunta($idPregunta);
        $nuevoNivelId = $this->nivelDao->obtenerIdNivelSegunRatio($ratio);

        $sql = 'UPDATE pregunta 
                SET nivel_id = ?
                WHERE id = ?';

        $types = 'ii';
        $params = [$nuevoNivelId, $idPregunta];

        return $this->conexion->executePrepared($sql, $types, $params) > 0;
    }
    public function obtenerRespuestaPorIdDeRespuesta($idRespuesta)
    {
        $sql = 'SELECT * FROM respuesta
                WHERE id = ?';
        $types = 'i';
        $params = [$idRespuesta];

        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        )[0];
    }
}