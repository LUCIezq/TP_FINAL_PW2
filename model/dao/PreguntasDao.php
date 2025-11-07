<?php

class PreguntasDao
{
    private MyConexion $conexion;

    public function __construct(MyConexion $conexion)
    {
        $this->conexion = $conexion;
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
        r.id as respuesta_id
        from pregunta p
        JOIN usuario u ON p.usuario_id = u.id
        LEFT JOIN respuesta r ON r.pregunta_id = p.id
        WHERE p.activa  =0";

        $result = $this->conexion->query($sql);

        $questions = [];

        foreach ($result as $row) {
            $pid = $row['pregunta_id'];

            if (!isset($questions[$pid])) {
                $questions[$pid] = [
                    'usuario' => $row['usuario'],
                    'pregunta' => $row['pregunta'],
                    'pregunta_id' => $pid,
                    'respuestas' => []
                ];
            }

            if (!empty($row['respuesta_id'])) {
                $questions[$pid]['respuestas'][] = [
                    'respuesta' => $row['respuesta'],
                    'respuesta_id' => $row['respuesta_id']
                ];
            }
        }
        //array values es util para pasar a mustache ya que este no maneja bien los arrays asociativos
        return array_values($questions);

    }

}