<?php

class GameDao
{
    private $dbConnection;
    private PreguntasDao $PreguntasDao;


    public function __construct($dbConnection, PreguntasDao $PreguntasDao)
    {

        $this->dbConnection = $dbConnection;
        $this->PreguntasDao = $PreguntasDao;
    }

}
