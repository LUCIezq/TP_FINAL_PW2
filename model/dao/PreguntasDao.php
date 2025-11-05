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

        $sql = "INSERT INTO pregunta (texto,categoria_id,puntos,activa,fecha_creacion) VALUES (?,?,?,?, NOW())";

        $params = [
            $data["pregunta"],
            $data["categoriaId"],
            100,
            0
        ];

        $types = "siii";

        $result = $this->conexion->executePrepared($sql, $types, $params);

        echo $result;

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

}