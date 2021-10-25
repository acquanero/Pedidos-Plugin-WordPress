<?php

//funcion para formatear la fecha de Y-M-D a D-M-Y
function reformatDate($fechaString)
{

    $dateWithNewFormat = '';

    if ($fechaString != null) {

        $delimitador = "-";
        $dateToArray = explode($delimitador, $fechaString);
        $dateWithNewFormat = $dateToArray[2] . '-' . $dateToArray[1] . '-' . $dateToArray[0];
    }

    return $dateWithNewFormat;
}

//funcion para formatear la fecha de h:m:s a h:m
function reformatHora($horaString)
{

    $dateWithNewFormat = '';

    if ($horaString != null) {

        $delimitador = ":";
        $dateToArray = explode($delimitador, $horaString);
        $dateWithNewFormat = $dateToArray[0] . ':' . $dateToArray[1];
    }

    return $dateWithNewFormat;
}

//funcion para formatear la fecha de D-M-Y a Y-M-D
function reformatDateToYMD($fechaString)
{

    $dateWithNewFormat = '';

    if ($fechaString != null) {

        $delimitador = "-";
        $dateToArray = explode($delimitador, $fechaString);
        $dateWithNewFormat = $dateToArray[0] . '-' . $dateToArray[1] . '-' . $dateToArray[2];
    }

    return $dateWithNewFormat;
}

//funcion para formatear la fecha de Y-M-D hh:mm:ss a D-M-Y hh:mm:ss 'hs'

function reformatDateTime($fechaHoraString)
{

    $dateWithNewFormat = '';

    if ($fechaHoraString != null) {

        $delimitador = " ";
        $dateToArray = explode($delimitador, $fechaHoraString);

        if (count($dateToArray) > 1) {

            $dateWithNewFormat = reformatDate($dateToArray[0]) . " " . reformatHora($dateToArray[1]) . " hs";

        }  else {

            $dateWithNewFormat = $fechaHoraString;

        }
        
    }

    return $dateWithNewFormat;
}

//Funcion para remover las hh:mm:ss de una fecha

function removeHoursMinutesSeconds($dateWithHoursMinutesSeconds){

    $delimitador = " ";

    $dateToArray = explode($delimitador, $dateWithHoursMinutesSeconds);

    $dateWithOutHoursMinutesSeconds = reformatDate($dateToArray[0]);

    return $dateWithOutHoursMinutesSeconds;

}

//Funcion para formatear la fecha de D-M-Y hh:mm:ss a Y-M-D hh:mm:ss (formato correcto para almacenar en la BD)
function reformatDateTimeToStoreInDB($yearMonthDayHours)
{

    $dateWithNewFormat = '';

    if ($yearMonthDayHours != null) {

        $delimitador = " ";
        $dateToArray = explode($delimitador, $yearMonthDayHours);


        $dmy = $dateToArray[0];
        $delimitadorYMD = "-";
        
        $ymdInArray = explode($delimitadorYMD, $dmy);
        $ymd = $ymdInArray[2] . '-' . $ymdInArray[1] . '-' . $ymdInArray[0];

        if (count($dateToArray) > 1) {

            $dateWithNewFormat = $ymd . ' ' . $dateToArray[1];

        } else {

            $dateWithNewFormat = $ymd;

        }
        
    }

    return $dateWithNewFormat;
}

//Funcion que le da formate con tabulacion y saltos al contenido de la tabla para general el xls de descarga
function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

?>
