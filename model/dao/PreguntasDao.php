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
        WHERE p.activa=0";

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

    public function actualizarPregunta($data, $id)
    {
    }

    public function rechazarPregunta($id)
    {
        $sql = "DELETE FROM pregunta WHERE id = ?";
        $params = [$id];
        $types = "i";
        return $this->conexion->executePrepared($sql, $types, $params) === 1;
    }

    public function getAllSystemQuestions()
    {
        $sql = "SELECT p.id as pregunta_id,
        p.texto as pregunta,
        u.nombre_usuario as usuario,
        p.genero_id,
        r.texto as respuesta,
        r.id as respuesta_id,
        r.es_correcta
        FROM pregunta p 
        JOIN usuario u ON p.usuario_id = u.id
        LEFT JOIN respuesta r on r.pregunta_id = p.id
        WHERE p.activa=true";

        $data = $this->conexion->query($sql);

        $questions = [];

        foreach ($data as $row) {
            $id = $row['pregunta_id'];

            if (!isset($questions[$id])) {
                $questions[$id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'pregunta' => $row['pregunta'],
                    'genero_id' => $row['genero_id'],
                    'usuario' => $row['usuario'],
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