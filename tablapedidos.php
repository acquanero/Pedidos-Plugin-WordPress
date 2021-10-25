<?php

include_once 'auxiliaryfunctions.php';

global $wpdb;

$slug = basename(get_permalink());


//Almaceno en variable el nombre de la tabla de productos
$tabla_productos = "{$wpdb->prefix}productos_en_pedidos";

//obtengo los roles del usuario
global $current_user;
$user_roles = (array) $current_user->roles;
$needed_role = 'administrator';

//varaible que determina la cantidad de filas por hoja
$items_per_page = 10;

//booleano que determina si se aplico algún filtro y por lo tanto aplicar paginacion o no
$filterApplied = false;

//inicializo el array con los datos de la tabla con un array vacio
$array_tabla_pedidos = array();

//nombre de las columnas de la tabla pedidos
$columnIdPedido = 'idPedido';
$columnUsuarioControl = 'Usuario_control';
$columnFechaControl = 'Fecha_control';
$columnUsuarioSalida = 'Usuario_salida';
$columnFechaSalida = 'Fecha_salida';
$columnMetodoDeEnvio = 'Metodo_de_envio';
$ColumnCantidadProductos = 'cantidadProductos';
$ColumnDescripcion = 'descripcion';
$ColumnFechaPedido = 'Fecha_pedido';
$ColumnFechaLlamada = 'Fecha_llamada';

//nombre de las columnas de la tabla productos
$productosColumnIdPedido = 'idPedido';
$productosColumnSku = 'sku';
$productosColumnDescripcion = 'descripcion';
$productosColumnCantidad = 'cantidadProductos';

//inicializo las variables que determinan si el ordenamiento es ascendente o descendente
$idPedidoOrder = "descen";
$usuarioControlOrder = "descen";
$fechaControlOrder = "descen";
$usuarioSalidaOrder = "descen";
$fechaSalidaOrder = "descen";
$metodoEnvioOrder = "descen";
$cantidadProductosOrder = "descen";
$descripcionOrder = "descen";
$fechaPedidoOrder = "descen";
$fechaLlamadaOrder = "descen";

//chequeo si el usuario tiene rol de administrador antes de continuar con el render de la tabla
if (!in_array($needed_role, $user_roles)) {

    echo '<div class="alert alert-danger" role="alert" id="alert-user-not-enabled">No cuenta con el permiso para esta página</div>';

    return;
}

//Defino el nombre de la tabla y la query que me trae todos los datos
$tabla_pedidos = "{$wpdb->prefix}picking_pedidos";
$mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnFechaPedido} DESC";


//chequeo si me vino un ID en el POST, de lo contrario dejo la query que trae la tabla entera

if (isset($_POST['searchForId'])) {

    $searchedId = $_POST['idPedido'];

    //en caso de escanear el codigo la cadena de caracteres debe ser parseada. El "]" delimita donde se ubica el ID 
    $mydelimitador = "[";


    //Controlo si el ID fue ingresado manualmente o con el scanner. Si fue con el Scanner hago el parseo para sacar el ID
    if (strpos($searchedId, $mydelimitador) !== false) {

        $mychain = explode($mydelimitador, $searchedId);
        $searchedId = $mychain[3];
    }

    //Luego del procesado el id debe haber quedado solo en un valor numerico, en caso de no serlo (aún contener caracteres) arroja error
    if (!is_numeric($searchedId)) {

        echo '<div class="alert alert-danger" role="alert" id="alert-user-not-enabled">' . $searchedId . ' no es un número de pedido válido.</div>';
        return;
    }

    $filterApplied = true;

    $mainQuery = "SELECT * FROM {$tabla_pedidos} WHERE {$columnIdPedido} = $searchedId";
}

//chequeo si me vino una fecha en el POST, de lo contrario dejo la query que trae la tabla entera
if (isset($_POST['searchByDate'])) {

    $filterApplied = true;

    $dateForQuery = $_POST['dateSelected'];

    $typeOfDateSelected = $_POST['radioForDate'];

    if ($typeOfDateSelected == 'control') {

        $mainQuery = "SELECT * FROM {$tabla_pedidos} WHERE DATE({$columnFechaControl}) = '{$dateForQuery}'";
    } else {

        $mainQuery = "SELECT * FROM {$tabla_pedidos} WHERE DATE({$columnFechaSalida}) = '${dateForQuery}'";
    }
};

if (isset($_POST['searchByMetodoEnvio'])) {

    $filterApplied = true;

    $metodoDeEnvio = $_POST['metodoEnvio'];

    $mainQuery = "SELECT * FROM {$tabla_pedidos} WHERE {$columnMetodoDeEnvio} LIKE '{$metodoDeEnvio}'";
};


//Chequeo si me llega el request de cerrar la tabla de productos
if (isset($_POST['closeTableProducts'])) {

    $showProducts = false;
}

//Chequeo si me llego solicitud de mostrar los productos de un pedido
if (isset($_GET['idpedidoforproductos']) && !isset($_POST['closeTableProducts'])) {

    $showProducts = true;

    $idPedidoForProductos = $_GET['idpedidoforproductos'];

    $queryForProductos = "SELECT * FROM {$tabla_productos} WHERE {$productosColumnIdPedido} = $idPedidoForProductos";

    $resultProducts = $wpdb->get_results($queryForProductos);
}

//Chequeo el query String recibido para determinar el orden en el cual se mostraran los datos
if (isset($_GET['column'])) {

    if ($_GET["column"] == "idPedido") {

        if ($_GET["idPedidoOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnIdPedido} ASC";

            $idPedidoOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnIdPedido} DESC";

            $idPedidoOrder = "ascen";
        }
    }

    if ($_GET["column"] == "usuarioControl") {

        if ($_GET["usuarioControlOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnUsuarioControl} ASC";

            $usuarioControlOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnUsuarioControl} DESC";

            $usuarioControlOrder = "ascen";
        }
    }

    if ($_GET["column"] == "fechaControl") {


        if ($_GET["fechaControlOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnFechaControl} ASC";

            $fechaControlOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnFechaControl} DESC";

            $fechaControlOrder = "ascen";
        }
    }

    if ($_GET["column"] == "usuarioSalida") {

        if ($_GET["usuarioSalidaOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnUsuarioSalida} ASC";

            $usuarioSalidaOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnUsuarioSalida} DESC";

            $usuarioSalidaOrder = "ascen";
        }
    }

    if ($_GET["column"] == "fechaSalida") {


        if ($_GET["fechaSalidaOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnFechaSalida} ASC";

            $fechaSalidaOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnFechaSalida} DESC";

            $fechaSalidaOrder = "ascen";
        }
    }

    if ($_GET["column"] == "cantidadProductos") {

        if ($_GET["cantidadProductosOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnCantidadProductos} ASC";

            $cantidadProductosOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnCantidadProductos} DESC";

            $cantidadProductosOrder = "ascen";
        }
    }

    if ($_GET["column"] == "descripcion") {

        if ($_GET["descripcionOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnDescripcion} ASC";

            $descripcionOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnDescripcion} DESC";

            $descripcionOrder = "ascen";
        }
    }

    if ($_GET["column"] == "metododeenvio") {

        if ($_GET["metodoDeEnvioOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnMetodoDeEnvio} ASC";

            $metodoEnvioOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$columnMetodoDeEnvio} DESC";

            $metodoEnvioOrder = "ascen";
        }
    }

    if ($_GET["column"] == "fechaPedido") {

        if ($_GET["fechaPedidoOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnFechaPedido} ASC";

            $fechaPedidoOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnFechaPedido} DESC";

            $fechaPedidoOrder = "ascen";
        }
    }

    if ($_GET["column"] == "fechaLlamada") {

        if ($_GET["fechaLlamadaOrder"] == "ascen") {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnFechaLlamada} ASC";

            $fechaLlamadaOrder = "descen";
        } else {

            $mainQuery = "SELECT * FROM {$tabla_pedidos} ORDER BY {$ColumnFechaLlamada} DESC";

            $fechaLlamadaOrder = "ascen";
        }
    }
};

//Fracciono los resultados para mostrar por paginas (teniendo en cuenta si se apicó algun filtro)
$total_query = "SELECT COUNT(*) FROM ${tabla_pedidos}";
$total = $wpdb->get_var($total_query);
$array_tabla_pedidos = $wpdb->get_results($mainQuery, ARRAY_A);
$page = isset($_GET['cpage']) ? abs((int) $_GET['cpage']) : 1;
$offset = ($page * $items_per_page) - $items_per_page;

//Si no se aplicó filtro, indico que se paginen los resultados
if ($filterApplied == true) {

    $results = $wpdb->get_results($mainQuery);
} else {

    $results = $wpdb->get_results($mainQuery . " LIMIT ${offset}, ${items_per_page}");
}

?>

<div class="container">

    <div class="row">
        <form method="POST">
            <label for="idPedido">Buscar por ID:</label>
            <div class="row">
                <div class="col align-self-start">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control numeroPedido" placeholder="Ingrese número de pedido" aria-label="Recipient's username" aria-describedby="basic-addon2" id="idPedido" name="idPedido">
                    </div>
                </div>
                <div class="col align-self-start">
                    <div class="input-group-append btn-validar">
                        <input type="submit" class="btn btn-outline-secondary" name="searchForId" id="searchForId" value="Buscar">
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="row">
        <form method="POST">
            <label for="dateSelected">Filtrar por Fecha:</label>
            <div class="row">

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="radioForDate" id="control" value="control" checked>
                    <label class="form-check-label" for="control">Fecha de Control</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="radioForDate" id="salida" value="salida">
                    <label class="form-check-label" for="salida">Fecha de Salida</label>
                </div>

            </div>
            <div class="row">
                <div class="col align-self-start">

                    <div class="input-group mb-3">
                        <input type="date" class="form-control" id="dateSelected" name="dateSelected">
                    </div>
                </div>
                <div class="col align-self-start">
                    <div class="input-group-append btn-validar">
                        <input type="submit" class="btn btn-outline-secondary" name="searchByDate" id="searchByDate" value="Buscar">
                    </div>
                </div>
            </div>

        </form>
    </div>

    <div class="row">
        <form method="POST">
            <label for="metodoEnvio">Filtrar por tipo de envio:</label>
            <div class="row">
                <div class="col align-self-start">

                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="metodoEnvio" name="metodoEnvio">
                    </div>
                </div>
                <div class="col align-self-start">
                    <div class="input-group-append btn-validar">
                        <input type="submit" class="btn btn-outline-secondary" name="searchByMetodoEnvio" id="searchByMetodoEnvio" value="Buscar">
                    </div>
                </div>
            </div>
        </form>
    </div>


    <div class="row">
        <div class="row">
            <div class="col-sm-auto align-self-start">

                <a href="<?php echo get_site_url() . '/' . $slug; ?>">
                    <button class="btn btn-outline-secondary" name="clear">Ver todos los registros</button>
                </a>



            </div>
            <div class="col-sm-auto align-self-start">
                <form method="POST">
                    <input type="hidden" name="prova" value="<?php

                                                                $flag = false;
                                                                foreach ($array_tabla_pedidos as $row) {
                                                                    if (!$flag) {
                                                                        echo implode("\t", array_keys($row)) . "\n";
                                                                        $flag = true;
                                                                    }
                                                                    array_walk($row, __NAMESPACE__ . '\cleanData');
                                                                    echo implode("\t", array_values($row)) . "\n";
                                                                }

                                                                ?>" />
                    <div class="buttonExportTable">
                        <button type="submit" class="btn btn-outline-secondary" name="exportData" id="exportData" value="Exportar tabla">Exportar tabla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="containerTabla">
            <?php
            if (!empty($results)) {

                echo '<table class="table table-sm table-striped">';
                echo '<thead>';
                echo "<tr>";
                echo '<th class="th_my_style"><a href="?column=idPedido&idPedidoOrder=' . $idPedidoOrder . '">Id Pedido</a></th>';
                echo '<th class="th_my_style"><a href="?column=cantidadProductos&cantidadProductosOrder=' . $cantidadProductosOrder . '">N° de productos</a></th>';
                //echo '<th class="th_my_style"><a href="?column=descripcion&descripcionOrder=' . $descripcionOrder . '">Descripcion</a></th>';
                echo '<th class="th_my_style"><a href="?column=metododeenvio&metodoDeEnvioOrder=' . $metodoEnvioOrder . '">Metodo de envio</a></th>';

                echo '<th class="th_my_style"><a href="?column=fechaPedido&fechaPedidoOrder=' . $fechaPedidoOrder . '">Fecha pedido</a></th>';
                //echo '<th class="th_my_style"><a href="?column=fechaLlamada&fechaLlamadaOrder=' . $fechaLlamadaOrder . '">Fecha llamada</a></th>';
                echo '<th class="th_my_style"><a href="?column=usuarioControl&usuarioControlOrder=' . $usuarioControlOrder . '">Usuario control</a></th>';
                echo '<th class="th_my_style"><a href="?column=fechaControl&fechaControlOrder=' . $fechaControlOrder . '">Fecha de control</a></th>';
                echo '<th class="th_my_style"><a href="?column=usuarioSalida&usuarioSalidaOrder=' . $usuarioSalidaOrder . '">Usuario salida</a></th>';
                echo '<th class="th_my_style"><a href="?column=fechaSalida&fechaSalidaOrder=' . $fechaSalidaOrder . '">Fecha de salida</a></th>';

                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                foreach ($results as $row) {
                    echo "<tr>";
                    echo '<td><a href="#" class="clickableIdPedido">' . $row->$columnIdPedido . "</a></td>";
                    echo '<td class="celdaAlignLeft">' . $row->$ColumnCantidadProductos . "</td>";
                    //echo '<td class="celdaAlignLeft">' . $row->$ColumnDescripcion . "</td>";
                    echo '<td class="celdaAlignLeft">' . $row->$columnMetodoDeEnvio . "</td>";
                    echo '<td class="celdaAlignLeft">' . reformatDateTime($row->$ColumnFechaPedido) . "</td>";
                    //echo '<td class="celdaAlignLeft">' . reformatDateTime($row->$ColumnFechaLlamada) . "</td>";
                    echo '<td class="celdaAlignLeft">' . $row->$columnUsuarioControl . "</td>";
                    echo '<td class="celdaAlignLeft">' . reformatDateTime($row->$columnFechaControl) . "</td>";
                    echo '<td class="celdaAlignLeft">' . $row->$columnUsuarioSalida . "</td>";
                    echo '<td class="celdaAlignLeft">' . reformatDateTime($row->$columnFechaSalida) . "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {

                echo '<div class="alert alert-danger" role="alert" id="alert-user-not-enabled">No se encontraron resultados</div>';
            }

            ?>

        </div>

    </div>


    <?php
    echo '<div class="row">';

    echo '<div class="text-center p-4">';


    if (!isset($_POST['searchByMetodoEnvio']) && !isset($_POST['searchForId']) && !isset($_POST['searchByDate'])) {

        echo paginate_links(array(
            'base' => add_query_arg('cpage', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($total / $items_per_page),
            'current' => $page
        ));

        echo '</div>';
        echo '</div>';
    }
    ?>

    <!-- The Modal -->
    <div id="popUpProductos" class="modal">

        <!-- Modal content -->
        <div class="modal-content">
            <h2>Productos incluidos en el pedido</h2>
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Id Pedido</th>
                        <th>SKU</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody id="bodyTablaProductos">
                    <!--Contenido de la tabla generado por tablapedidos.js -->
                </tbody>
            </table>
            <div>
                <button id="cerrarProductos">Cerrar tabla</button>
            </div>
        </div>
    </div>

</div>
