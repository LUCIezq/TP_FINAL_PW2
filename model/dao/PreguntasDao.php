<?php

class PreguntasDao
{
    private MyConexion $conexion;
    private CategoryDao $categoryDao;

    public function __construct(MyConexion $conexion, CategoryDao $categoryDao)
    {
        $this->conexion = $conexion;
        $this->categoryDao = $categoryDao;
    }

    public function getPreguntasPorCategoria($idCategoria)
    {
        $sql = "SELECT * FROM pregunta WHERE genero_id = ?";
        $params = [$idCategoria];
        $types = "i";

        $result = $this->conexion->executePrepared($sql, $types, $params);

        return $this->conexion->processData($result);
    }

    public function createQuestion($data)
    {

        foreach ($data as $value) {
            if (empty($value)) {
                return [
                    "created" => false,
                    "lastInsertId" => null
                ];
            }
        }

        $sql = "INSERT INTO pregunta (genero_id,dificultad_id,texto,activa,usuario_id) VALUES (?,?,?,?,?)";

        $params = [
            $data["categoriaId"],
            1,
            $data["pregunta"],
            0,
            $data["usuarioId"]
        ];

        $types = "iisii";

        $result = $this->conexion->executePrepared($sql, $types, $params);

        return [
            "created" => $result != 0,
            "lastInsertId" => $result
        ];
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

        $this->conexion->executePrepared($sql, $types, $params);
    }

    public function getQuestionsWithFilter($filters)
    {
        $category = $filters['category_name'] ?? 'todas';

        if (!$category || $category === 'todas') {
            $filter = '1=1';
            $types = '';
            $params = [];
        } else {
            $filter = 'g.nombre = ?';
            $types = 's';
            $params = [$category];
        }

        $sql = "SELECT 
            u.nombre_usuario AS usuario,
            p.texto          AS pregunta,
            p.id             AS pregunta_id,
            r.texto          AS respuesta,
            r.id             AS respuesta_id,
            p.genero_id      AS genero_id,
            r.es_correcta    AS es_correcta,
            rep.id_reporte
        FROM pregunta p
        JOIN usuario u       ON p.usuario_id = u.id
        JOIN genero g        ON g.id = p.genero_id
        LEFT JOIN respuesta r ON r.pregunta_id = p.id
        LEFT JOIN reporte rep ON rep.id_reporte = (
            SELECT r2.id_reporte
            FROM reporte r2
            WHERE r2.id_pregunta = p.id
            ORDER BY r2.id_reporte DESC
                LIMIT 1
            )
            WHERE $filter
            ORDER BY rep.id_reporte DESC, p.id DESC";

        $result = $this->conexion->executePrepared($sql, $types, $params);

        $questions = [];

        foreach ($result as $row) {
            $id = $row['pregunta_id'];

            if (!isset($questions[$id])) {
                $questions[$id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'pregunta' => $row['pregunta'],
                    'genero_id' => $row['genero_id'],
                    'usuario' => $row['usuario'],
                    'respuestas' => [],
                    'id_reporte' => $row['id_reporte'] ?? null
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

    public function getAllQuestionByUsers()
    {
        $sql = "SELECT u.nombre_usuario as usuario,
        p.texto as pregunta,
        p.id as pregunta_id,
        r.texto as respuesta,
        r.id as respuesta_id,
        p.genero_id as genero_id,
        r.es_correcta as es_correcta
        from pregunta p
        JOIN usuario u ON p.usuario_id = u.id
        LEFT JOIN respuesta r ON r.pregunta_id = p.id
        WHERE p.usuario_id != 2 and p.activa != 1"; // 2 es el id del admin

        $result = $this->conexion->query($sql);

        if (empty($result))
            return [];

        $questions = [];

        foreach ($result as $row) {
            $pid = $row['pregunta_id'];

            if (!isset($questions[$pid])) {
                $questions[$pid] = [
                    'usuario' => $row['usuario'],
                    'pregunta' => $row['pregunta'],
                    'pregunta_id' => $pid,
                    'genero_id' => $row['genero_id'],
                    'genero_name' => $this->categoryDao->getById($row['genero_id'])['nombre'],
                    'respuestas' => [],
                ];
            }

            if (!empty($row['respuesta_id'])) {
                $questions[$pid]['respuestas'][] = [
                    'respuesta' => $row['respuesta'],
                    'respuesta_id' => $row['respuesta_id'],
                    'es_correcta' => $row['es_correcta']
                ];
            }
        }
        //array values es util para pasar a mustache ya que este no maneja bien los arrays asociativos
        return array_values($questions);

    }

    public function obtenerPreguntaPorId($id)
    {
        $sql = "SELECT 
                p.id AS pregunta_id,
                p.texto AS pregunta_texto,
                p.activa,
                g.id AS genero_id,
                g.nombre AS genero_nombre,
                r.id AS respuesta_id,
                r.texto AS respuesta_texto
            FROM pregunta p
            JOIN genero g ON g.id = p.genero_id
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
            'activa' => $rows[0]['activa'],
            'genero_id' => $rows[0]['genero_id'],
            'genero_nombre' => $rows[0]['genero_nombre'],
            'respuestas' => []
        ];

        foreach ($rows as $row) {
            $pregunta['respuestas'][] = [
                'id' => $row['respuesta_id'],
                'texto' => $row['respuesta_texto']
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

    public function aprobarPregunta($id)
    {
        $sql = 'UPDATE pregunta set activa=1 where id = ?';
        $params = [$id];
        $types = 'i';
        return $this->conexion->executePrepared($sql, $types, $params) === 1;
    }

    public function actualizarPregunta($data)
    {
        $cambios = [];
        $params = [];
        $types = '';
        $hayCambios = false;

        $preguntaForm_id = $data['pregunta_id'];
        $textoForm = $data['texto'];
        $genero_idForm = $data['genero_id'];
        $id_correctaForm = $data['id_correcta'];
        $respuestasForm = $data['respuestas'];

        $preguntaEnBd = $this->obtenerPreguntaPorId($preguntaForm_id);

        if (!$preguntaEnBd) {
            return 'Pregunta invalida.';
        }

        if ($preguntaEnBd['texto'] !== $textoForm) {
            $cambios[] = 'texto = ?';
            $params[] = $textoForm;
            $types .= 's';
        }

        $generoPreguntaBd = $preguntaEnBd['genero_id'];
        $generos = $this->categoryDao->getAll();

        if (!in_array($genero_idForm, array_column($generos, 'id'))) {
            return 'Genero invalido.';
        }

        if ($generoPreguntaBd !== (int) $genero_idForm) {
            $cambios[] = 'genero_id = ?';
            $params[] = $genero_idForm;
            $types .= 'i';
        }

        $respuestaCorrectaBd = $this->obtenerRespuestaCorrectaPorIdPregunta($preguntaEnBd['id']);

        if ($respuestaCorrectaBd['id'] != $id_correctaForm) {
            $this->actualizarRespuestaCorrecta($id_correctaForm, $preguntaEnBd['id']);
            $hayCambios = true;
        }

        $respuestasBd = $this->obtenerRespuestasPorId($preguntaEnBd['id']);

        foreach ($respuestasBd as $respuestaBd) {
            $respuestaId = $respuestaBd['id'];
            if (isset($respuestasForm[$respuestaId]) && $respuestasForm[$respuestaId] !== $respuestaBd['texto']) {
                $this->actualizarEnunciadoRespuesta($respuestaId, $respuestasForm[$respuestaId]);
                $hayCambios = true;
            }
        }

        if (empty($cambios) && !$hayCambios) {
            return 'No hay cambios para actualizar.';
        }

        if (count($cambios) > 0) {

            $sql = 'UPDATE pregunta set ' . implode(',', $cambios) . ' where id = ?';

            $this->conexion->executePrepared($sql, $types . 'i', [...$params, $preguntaForm_id]);
        }

        return "Pregunta actualizada correctamente.";
    }

    public function inactivarPregunta($id)
    {
        $sql = 'UPDATE pregunta set activa=0 where id = ?';
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

    public function obtenerRespuestasPorId($preguntaId)
    {
        $sql = "SELECT id,texto
        from respuesta 
        where pregunta_id = ?";

        $params = [$preguntaId];
        $types = "i";
        return $this->conexion->processData(
            $this->conexion->executePrepared($sql, $types, $params)
        );
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

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

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
        p.activa
        
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
                    'activa' => $row['activa'],
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
}