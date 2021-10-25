<?php

global $wpdb;

//obtengo los roles y nombre del usuario
global $current_user;
$user_roles = (array) $current_user->roles;
$user_name = $current_user->user_login;

//almaceno en variables los roles de los usuarios que operaran
$role_controller = 'control';
$role_distpacher = 'salida';

//Cambio el titulo de la pagina segun el rol del usuario
$page_title = "";

if (in_array($role_controller, $user_roles)) {
    $page_title = "Control de pedidos";
} else {
    if (in_array($role_distpacher, $user_roles)) {
        $page_title = "Salida de pedidos";
    };
};

$tabla_pedidos = "{$wpdb->prefix}picking_pedidos";

function getCurrentDate()
{
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $lafecha = date("Y") . "-" . date("m") . "-" . date("d") . " " . date("H") . ":" . date("i") . ":" . date("s");
    return $lafecha;
}

//guardo en un variable la tabla que lleva los conteos
$tabla_contadora = "{$wpdb->prefix}usuarios_contadores";

//Acceso a la tabla que llevará el conteo de los pedidos controlados y despachados

if (!isset($_POST['submitData'])) {

    //query para chequear si el usuario ya tiene una entrada en la tabla contadora
    $queryToCheckUserExistence = "SELECT * FROM {$tabla_contadora} WHERE Usuario = '{$user_name}'";

    $usuario_en_tabla_contadora = $wpdb->get_results($queryToCheckUserExistence, ARRAY_A);

    //chequeo si la consulta vuelve vacia. De ser asi creo una entrada para el usuario. De lo contrario, setteo el contador del usuario existente en cero
    if (empty($usuario_en_tabla_contadora)) {

        $data_to_insert = array(
            'Usuario' => $user_name,
            'Contador' => 0
        );
        $wpdb->insert($tabla_contadora, $data_to_insert);
    } else {

        $data_update = array(
            'Contador' => 0
        );

        $data_where = array('Usuario' => $user_name);

        $wpdb->update($tabla_contadora, $data_update, $data_where);
    }
}

//Si en el POST me llega resetCounter pongo en cero el contador
if (isset($_POST['resetCounter'])) {

    $data_update = array(
        'Contador' => 0
    );

    $data_where = array('Usuario' => $user_name);

    $wpdb->update($tabla_contadora, $data_update, $data_where);
}


//Funcion que accede al contador de la base de datos y lo incrementa
function incrementCounter($dbmanager, $tabla, $usuario)
{

    //modifico el contador en la BD
    $queryToGetCounter = "SELECT Contador FROM {$tabla} WHERE Usuario = '{$usuario}'";
    $conteoFromDB = $dbmanager->get_results($queryToGetCounter, ARRAY_A);
    $conteoActual = $conteoFromDB[0]['Contador'];
    $conteoIncrementado = $conteoActual + 1;

    $data_update = array(
        'Contador' => $conteoIncrementado
    );

    $data_where = array('Usuario' => $usuario);
    $dbmanager->update($tabla, $data_update, $data_where);
}

?>


<div class="container">
    <div class="row">
        <div class="col-12">
            <?php
            echo '<h1>' . $page_title . '</h1>';
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-10">
            <form method="POST">
                <div class="input-group mb-3">
                    <input type="text" class="form-control numeroPedido" placeholder="Ingrese número de pedido" aria-label="Recipient's username" aria-describedby="basic-addon2" id="idPedido" name="pedidoId">
                    <div class="input-group-append btn-validar">
                        <input type="submit" class="btn btn-outline-secondary" name="submitData" id="submitData" value="Validar">
                    </div>
                </div>
            </form>
            <?php
            //chequeo que el POST no este vacio
            if (isset($_POST['submitData'])) {
                //Obtengo el nombre de usuario
                $user_name = $current_user->user_login;
                //para obtener los datos del POST lo hago a traves del name
                $id = $_POST['pedidoId'];

                //en caso de escanear el codigo la cadena de caracteres debe ser parseada. El "]" delimita donde se ubica el ID 
                $mydelimitador = "[";


                //Controlo si el ID fue ingresado manualmente o con el scanner. Si fue con el Scanner hago el parseo para sacar el ID
                if (strpos($id, $mydelimitador) !== false) {

                    $mychain = explode($mydelimitador, $id);
                    $id = $mychain[3];
                }

                //Luego del procesado el id debe haber quedado solo en un valor numerico, en caso de no serlo (aún contener caracteres) arroja error y no continua ejecucion
                if (!is_numeric($id)) {

                    echo '<span class="dangerBox">';
                    echo $id . ' no es un número de pedido válido';
                    echo '</span>';

                    echo '<script>playAlert("notexists");</script>';


                } else {

                    //formulo la query que chequeará la existencia de los pedidos
                    $queryToCheckExistence = "SELECT * FROM {$tabla_pedidos} WHERE idPedido = '{$id}'";
                    //Chequeo si es usuario con rol 'Control' antes de realizar la consulta
                    if (in_array($role_controller, $user_roles)) {
                        //realizo la consulta a la BD con el metodo get_results de wordpress y lo convierto en array con el segundo parametro
                        $datos_pedido = $wpdb->get_results($queryToCheckExistence, ARRAY_A);
                        //chequeo si la consulta vuelve vacia, de ser asi lanazo inserto el nuevo pedido y lo controlo
                        if (empty($datos_pedido)) {

                            $data_to_insert = array(
                                'idPedido' => $id,
                                'Usuario_control' => $user_name,
                                'Fecha_control' => getCurrentDate()
                            );
                            $dbresponse = $wpdb->insert($tabla_pedidos, $data_to_insert);

                            if ($dbresponse == false) {

                                echo '<span class="dangerBox">';
                                echo 'Error al realizar la consulta en la base de datos';
                                echo '</span>';

                                echo '<script>playAlert("notexists");</script>';

                            } else {

                                echo '<span class="successBox">';
                                echo 'Pedido con ID ' . $id . ' ingresado y controlado';
                                echo '</span>';

                                echo '<script>playAlert("success");</script>';

                            }

                            //modifico el contador en la BD
                            incrementCounter($wpdb, $tabla_contadora, $user_name);
                        } else {
                            //en caso de existir un pedido con ese ID, chequeo si ya fue controlado
                            if ($datos_pedido[0]["Fecha_control"] == null) {
                                $data_update = array(
                                    'Fecha_control' => getCurrentDate(),
                                    'Usuario_control' => $user_name
                                );
                                $data_where = array('idPedido' => $id);
                                $dbresponse = $wpdb->update($tabla_pedidos, $data_update, $data_where);

                                if ($dbresponse == false) {

                                    echo '<span class="dangerBox">';
                                    echo 'Error al realizar la consulta en la base de datos';
                                    echo '</span>';

                                    echo '<script>playAlert("notexists");</script>';

                                } else {

                                    echo '<span class="successBox">';
                                    echo 'Pedido ' . $id . ' controlado correctamente';
                                    echo '</span>';

                                    echo '<script>playAlert("success");</script>';

                                    //modifico el contador en la BD
                                    incrementCounter($wpdb, $tabla_contadora, $user_name);

                                }

                            } else {
                                echo '<span class="dangerBox">';
                                echo 'El pedido con ID número ' . $id . ' ya se controló el ' . reformatDateTime($datos_pedido[0]["Fecha_control"]);
                                echo '</span>';

                                echo '<script>playAlert("repeated");</script>';
                            }
                        }
                    } elseif (in_array($role_distpacher, $user_roles)) {

                        $datos_pedido = $wpdb->get_results($queryToCheckExistence, ARRAY_A);

                        //chequeo si la consulta vuelve vacia, de ser asi lanzo error. De lo contrario marco la fecha de salida
                        if (empty($datos_pedido)) {
                            echo '<span class="dangerBox">';
                            echo 'No existe el pedido con ID: ' . $id;
                            echo '</span>';
                            echo '<script>playAlert("notexists");</script>';

                        } else {
                            //chequeo si el pedido ya fue despachado
                            if ($datos_pedido[0]["Fecha_salida"] == null) {
                                //chequeo si el pedido fue controlado antes de despachar
                                if ($datos_pedido[0]["Fecha_control"] == null) {
                                    echo '<span class="dangerBox">';
                                    echo 'El pedido con ID ' . $id . ' no puede ser despachado ya que no fue controlado ';
                                    echo '</span>';

                                    echo '<script>playAlert("notexists");</script>';
                                } else {

                                    $data_update = array(
                                        'Usuario_salida' => $user_name,
                                        'Fecha_salida' => getCurrentDate()
                                    );
                                    $data_where = array('idPedido' => $id);
                                    $dbresponse = $wpdb->update($tabla_pedidos, $data_update, $data_where);

                                    if ($dbresponse == false) {

                                        echo '<span class="dangerBox">';
                                        echo 'Error al realizar la consulta en la base de datos';
                                        echo '</span>';

                                        echo '<script>playAlert("notexists");</script>';
                                        
                                    } else {

                                        echo '<span class="successBox">';
                                        echo 'Pedido con ID ' . $id . ' despachado correctamente';
                                        echo '</span>';

                                        echo '<script>playAlert("success");</script>';
    
                                        //modifico el contador en la BD
                                        incrementCounter($wpdb, $tabla_contadora, $user_name);

                                    }
                                }
                            } else {
                                echo '<span class="dangerBox">';
                                echo 'El pedido con ID ' . $id . ' ya fue despachado el ' . reformatDateTime($datos_pedido[0]["Fecha_salida"]);
                                echo '</span>';

                                echo '<script>playAlert("repeated");</script>';
                            }
                        }
                    } else {
                        echo '<span class="dangerBox">';
                        echo 'Usuario no habilitado';
                        echo '</span>';

                        echo '<script>playAlert("notexists");</script>';

                    };
                }
            }
            ?>
        </div>
        <div class="col-2 colContador">
            <div class="cajaContador">

                <?php

                //query para traer el conteo
                $queryToGetCounter = "SELECT Contador FROM {$tabla_contadora} WHERE Usuario = '{$user_name}'";

                $conteoFromDB = $wpdb->get_results($queryToGetCounter, ARRAY_A);

                $conteoAMostrar = $conteoFromDB[0]['Contador'];

                echo '<div class="numeroContador">' . $conteoAMostrar . '</div>';

                ?>

            </div>

            <form method="POST">
                <input type="submit" class="btn btn-outline-secondary" name="resetCounter" id="resetCounter" value="Reset">
            </form>

        </div>
    </div>
</div>
