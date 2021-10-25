<?php
/*
Plugin para hacer el seguimiento de entrega de pedidos.
*/

include_once 'auxiliaryfunctions.php';


//Token aceptado (ingresar el tokken)
$acceptedToken = '';


function activatePluginPedido()
{

    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}picking_pedidos(
`idPedido` VARCHAR(45) NOT NULL ,
`cantidadProductos` INT NOT NULL ,
`descripcion` VARCHAR(45) NULL,
`Metodo_de_envio` VARCHAR(45) NULL,
`Fecha_pedido` DATETIME NULL,
`Fecha_llamada` DATETIME NULL,
`Usuario_control` VARCHAR(45) NULL,
`Fecha_control` DATETIME NULL,
`Usuario_salida` VARCHAR(45) NULL,
`Fecha_salida` DATETIME NULL,
PRIMARY KEY (`idPedido`));";

    $sqlCreateIndex = "CREATE INDEX `indice_fechas` ON {$wpdb->prefix}picking_pedidos (`Fecha_control`, `Fecha_salida`);";
    
    //Sentencias SQL que fueron realizando las modificaciones en la estructura en la base de datos
    //En caso de arrancar de cero con una nueva BDD, no son necesarias porque la creacion de la nueva BDD
    //ya tiene en cuenta estos cambios
    
    // $sqlCreateColumnDescripcion = "ALTER TABLE {$wpdb->prefix}picking_pedidos ADD COLUMN IF NOT EXISTS descripcion VARCHAR(255) AFTER sku";
    // $sqlCreateColumnFechaPedido = "ALTER TABLE {$wpdb->prefix}picking_pedidos ADD COLUMN IF NOT EXISTS Fecha_pedido DATETIME AFTER descripcion";
    // $sqlCreateColumnFechaLlamada = "ALTER TABLE {$wpdb->prefix}picking_pedidos ADD COLUMN IF NOT EXISTS Fecha_llamada DATETIME AFTER Fecha_pedido";
    // $sqlCreateColumnMetodoEnvio = "ALTER TABLE {$wpdb->prefix}picking_pedidos ADD COLUMN IF NOT EXISTS Metodo_de_envio VARCHAR(45) AFTER descripcion";
    // $sqlCreateNewColumnSku = "ALTER TABLE {$wpdb->prefix}picking_pedidos ADD COLUMN IF NOT EXISTS sku VARCHAR(255) AFTER idPedido";

    $wpdb->query($sql);
    $wpdb->query($sqlCreateIndex);
    // $wpdb->query($sqlCreateColumnDescripcion);
    // $wpdb->query($sqlCreateNewColumnSku);
    // $wpdb->query($sqlCreateColumnFechaPedido);
    // $wpdb->query($sqlCreateColumnFechaLlamada);
    // $wpdb->query($sqlCreateColumnMetodoEnvio);

    $sqlCreateTableContador = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}usuarios_contadores(
        `Id` INT NOT NULL AUTO_INCREMENT, 
        `Usuario` VARCHAR(45) NOT NULL,
        `Contador` INT NULL,
        PRIMARY KEY (`id`));";

    $wpdb->query($sqlCreateTableContador);

    $sqlCreateTableProductos = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}productos_en_pedidos(
        `idPedido` VARCHAR(255) NOT NULL , 
        `sku` VARCHAR(255) NOT NULL,
        `descripcion` VARCHAR(255) NULL,
        `cantidadProductos` INT NULL,
        PRIMARY KEY (`idPedido`, `sku`));";

    $wpdb->query($sqlCreateTableProductos);

    //Alterar tablas para cambiar el formato del campo idPedido de int a Varchar (para el caso de versiones viejas. Si se inicia de cero la Base de datos
    //no son necesarias estas sentencias)
    $sqlChangeIdPedidoToVarCharInPicking = "ALTER TABLE {$wpdb->prefix}picking_pedidos CHANGE `idPedido` `idPedido` VARCHAR(255) NOT NULL";
    $sqlChangeIdPedidoToVarCharInProductos = "ALTER TABLE {$wpdb->prefix}productos_en_pedidos CHANGE `idPedido` `idPedido` VARCHAR(255) NOT NULL";

    $wpdb->query($sqlChangeIdPedidoToVarCharInPicking);
    $wpdb->query($sqlChangeIdPedidoToVarCharInProductos);

    //Cambio en tabla picking_pedidos para la columna sku transformarla en el contador de productos (para el caso de versiones viejas. Si se inicia de cero la Base de datos
    //no es necesaria esta sentencia)
    $sqlChangeSkuToCantidadProductos = "ALTER TABLE {$wpdb->prefix}picking_pedidos CHANGE `sku` `cantidadProductos` INT NULL";
    $wpdb->query($sqlChangeSkuToCantidadProductos);
    
    //Cambio en tabla productos_en_pedidos para agregar contador)
    $sqlAddToCantidadProductos = "ALTER TABLE {$wpdb->prefix}productos_en_pedidos ADD `cantidadProductos` INT NULL";
    $wpdb->query($sqlAddToCantidadProductos);

    //Sentencia SQL para popular con los datos de la cantidad de productos de los pedidos existentes antes de la actualizacion

    //UPDATE wp_picking_pedidos SET wp_picking_pedidos.cantidadProductos = 
    //(SELECT COUNT(*) FROM wp_productos_en_pedidos WHERE wp_productos_en_pedidos.idPedido LIKE wp_picking_pedidos.idPedido);

    $sqlCreateIndexPedidos = "CREATE INDEX `indice_idPedido` ON {$wpdb->prefix}productos_en_pedidos (`idPedido`);";
    $wpdb->query($sqlCreateIndexPedidos);


}

function deactivatePluginPedido(){

}

register_activation_hook(__FILE__, 'activatePluginPedido');
register_deactivation_hook(__FILE__,'deactivatePluginPedido');

add_role('control','Control',array('read'=> true,'read_posts'=> true,'read_pages'=> true));
add_role('salida','Salida',array('read'=> true,'read_posts'=> true,'read_pages' => true));

add_shortcode( 'pedidos_tag' , 'renderPedidos');
add_shortcode('tablapedidos_tag', 'renderTabla');

function renderPedidos(){
    wp_enqueue_script('bootstrapJs', plugins_url('bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));
    wp_enqueue_style('bootstrapCSS', plugins_url('bootstrap/css/bootstrap.min.css',__FILE__));
    wp_enqueue_script('JsExterno', plugins_url('myjs/pedidoscontroller.js',__FILE__),array('jquery'));
    wp_enqueue_style('myCSS', plugins_url('mystyles.css',__FILE__));
    include plugin_dir_path(__FILE__).'pedidoscontroller.php';
}

function renderTabla(){
    wp_enqueue_script('bootstrapJs', plugins_url('bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));
    wp_enqueue_style('bootstrapCSS', plugins_url('bootstrap/css/bootstrap.min.css',__FILE__));
    wp_enqueue_script('JsExternoParaTablaPedidos', plugins_url('myjs/tablapedidos.js',__FILE__),array('jquery'));
    wp_enqueue_style('myCSS', plugins_url('mystyles.css',__FILE__));
    include plugin_dir_path(__FILE__).'tablapedidos.php';
}

add_action('admin_menu', 'createMenuPedidos');

function createMenuPedidos(){

    add_menu_page(
        'Pedidos',//Titulo de la pagina
        'Pedidos', //Titulo del menu
        'manage_options',//que usuarios tendran permiso
        plugin_dir_path(__FILE__).'pedidoscontroller.php',//slug
        null,//funcion que muestra el contenido
        plugin_dir_url(__FILE__).'/img/icon.png',
        '2'
    );
}

//funcion para cargar bootstrap, css y JS propios

function cargarJSfromBootstrap($hook){

    //echo "<script>console.log('$hook')</script>";

    if ($hook != "pluginbiggerpedidos/pedidoscontroller.php"){
        return;
    }

    wp_enqueue_script('bootstrapJs', plugins_url('bootstrap/js/bootstrap.min.js',__FILE__),array('jquery'));

}

function cargarCSSfromBootstrap($hook){

    if ($hook != "pluginbiggerpedidos/pedidoscontroller.php"){
        return;
    }

    wp_enqueue_style('bootstrapCSS', plugins_url('bootstrap/css/bootstrap.min.css',__FILE__));

}

function cargarMyJs($hook){

    if ($hook != "pluginbiggerpedidos/pedidoscontroller.php"){
        return;
    }

    wp_enqueue_script('JsExterno', plugins_url('myjs/pedidoscontroller.js',__FILE__),array('jquery'));

}

function cargarMyCss($hook){

    if ($hook != "pluginbiggerpedidos/pedidoscontroller.php"){
        return;
    }

    wp_enqueue_style('myCSS', plugins_url('mystyles.css',__FILE__));

}

function cargarTablaPedidosJs($hook){

    if ($hook != "pluginbiggerpedidos/tablapedidos.php"){
        return;
    }

    wp_enqueue_script('JsExternoParaTablaPedidos', plugins_url('myjs/tablapedidos.js',__FILE__),array('jquery'));

}

//funciones triggerDownloadTable y sendFile, chequean si llego un post solicitando descarga y cambia los headers para activar la descarga del .xls
function triggerDownloadTable()
{

    if (isset($_POST['exportData'])) {

        $filename = "tabla_pedidos" . date('Ymd') . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");

        echo $_POST['prova'];

        exit();
    }
}



//Endpoint para agregar o modificar pedidos
function addModifyPedidosEndPoint($data)
{

    global $acceptedToken;

    $recivedToken = $_SERVER['HTTP_TOKEN'];

    $serverResponse = array(
        "msg" => "Error al insertar el pedido",
    );

    $operacionExitosa = true;

    $rta = 'sin respuesta';


    if ($acceptedToken == $recivedToken){

        $listaPedidos = $data['listaPedidos'];

        foreach ($listaPedidos as $unPedido) {
            try
            {
                $recivedIdPedido = $unPedido['idPedido'];
                $recivedSku = $unPedido['sku'];
                $recivedDescripcion = $unPedido['descripcion'];
                $recivedMetodoEnvio = $unPedido['metodoEnvio'];
                $recivedFechaHoraPedido = $unPedido['fechaHoraPedido'];
                $recivedFechaHoraLlamada = $unPedido['fechaHoraLlamada'];
                $recivedCantidad = $unPedido['recivedCantidad'];
                $recivedFechaHoraPedido = reformatDateTimeToStoreInDB($recivedFechaHoraPedido);
                $recivedFechaHoraLlamada = reformatDateTimeToStoreInDB($recivedFechaHoraLlamada);
                global $wpdb;
                //Chequeo si el pedido existe en la tabla pedidos
                $sqlCheckId = "SELECT idPedido FROM {$wpdb->prefix}picking_pedidos WHERE idPedido LIKE '{$recivedIdPedido}'";
                $resultOfExistensCehck = $wpdb->query($sqlCheckId);
    
                if ($resultOfExistensCehck == null) {
    
                    $sqlQuery = "INSERT INTO {$wpdb->prefix}picking_pedidos (`idPedido`, `descripcion`, `Metodo_de_envio`, `Fecha_pedido`, 
                    `Fecha_llamada`) 
                    VALUES ('{$recivedIdPedido}', '{$recivedDescripcion}', '{$recivedMetodoEnvio}', '{$recivedFechaHoraPedido}', '{$recivedFechaHoraLlamada}')";
    
                    $rta = $wpdb->query($sqlQuery);
    
                    if ($rta == false){
    
                        $operacionExitosa = false;
                    }
    
                } else {
    
                    $sqlQueryToUpdatePedido = "UPDATE {$wpdb->prefix}picking_pedidos SET 
                    `descripcion` = '{$recivedDescripcion}', 
                    `Metodo_de_envio` = '{$recivedMetodoEnvio}', 
                    `Fecha_pedido` = '{$recivedFechaHoraPedido}', 
                    `Fecha_llamada` = '{$recivedFechaHoraLlamada}' 
                    WHERE idPedido LIKE '{$recivedIdPedido}'";
    
                    $rta = $wpdb->query($sqlQueryToUpdatePedido);
    
                    if ($rta == false){
    
                        $operacionExitosa = false;
                    }
                }
                //Chequeo si el pedido existe en la tabla productos
                $sqlCheckId = "SELECT idPedido FROM {$wpdb->prefix}productos_en_pedidos WHERE idPedido LIKE '{$recivedIdPedido}' AND sku LIKE '{$recivedSku}' ";
                $resultOfExistensCehck = $wpdb->query($sqlCheckId);
    
                if ($resultOfExistensCehck == null) {
    
                    $sqlQuery = "INSERT INTO {$wpdb->prefix}productos_en_pedidos (`idPedido`, `sku`, `descripcion`,`cantidadProductos` ) VALUES ('{$recivedIdPedido}', '{$recivedSku}', '{$recivedDescripcion}','{$recivedCantidad}')";
    
                    $rta = $wpdb->query($sqlQuery);
    
                    if ($rta == false){
    
                        $operacionExitosa = false;
                    }
    
                } else {
                    $sqlQueryToUpdateProductoEnPedido = "UPDATE {$wpdb->prefix}productos_en_pedidos 
                    SET
                        `descripcion` = '{$recivedDescripcion}',
                        `cantidadProductos` = '{$recivedCantidad}' 
                    WHERE 
                        idPedido LIKE '{$recivedIdPedido}' 
                        AND
                        `sku` LIKE '{$recivedSku}'";
    
                    $rta = $wpdb->query($sqlQueryToUpdateProductoEnPedido);
    
                    if ($rta == false){
                        $operacionExitosa = false;
                    }
                }//else
            }
            catch(Exception $e)
            {
                
            }

            //Actualizacion de la cantidad de productos de un pedido
            $cantidadProductos ="1";
            try
            {
                $sqlToCountNumberOfProducts = "SELECT COUNT(*) FROM {$wpdb->prefix}productos_en_pedidos WHERE idPedido LIKE '{$recivedIdPedido}'";
                $cantidadProductosArray = $wpdb->get_results($sqlToCountNumberOfProducts, ARRAY_N);
                $cantidadProductos = $cantidadProductosArray[0][0];
            }
            catch(Exception $e)
            {
                
            }
            $sqlToUpdateNumberOfProducts = "UPDATE {$wpdb->prefix}picking_pedidos SET
                    `cantidadProductos` = '{$cantidadProductos}' 
                WHERE 
                    idPedido LIKE '{$recivedIdPedido}'";
            $wpdb->query($sqlToUpdateNumberOfProducts);
        }//foreach

    } else {

        return new WP_Error('Unauthorized ', __('Acceso denegado'), array('status' => 401));

        $rta = 'cinco';

        $serverResponse = array(
            "msg" => "Sin autorizaci贸n",
        );
    }

    if ($operacionExitosa == true){

        $serverResponse = array(
            "msg" => "Pedidos insertados exitosamente",
        );


    }

    return $serverResponse;
}




//Endpoint para obtener los pedidos depachados entre dos fechas
function getPedidosDespachados($data){

    global $acceptedToken;

    $recivedToken = $_SERVER['HTTP_TOKEN'];

    $serverResponse = array(
        "msg" => "Error al insertar el pedido",
    );

    
    $initialDate = $data['initialDate'];
    $finalDate = $data['finalDate'];

    if ($acceptedToken == $recivedToken){

        if($initialDate == null || $finalDate == null){

            return new WP_Error( 'Bad Request ', __('Solicitud incorrecta'), array( 'status' => 400 ) );
    
        } else {

            $initialDate = reformatDateTimeToStoreInDB($initialDate);
            $finalDate = reformatDateTimeToStoreInDB($finalDate);
    
            global $wpdb;
    
            $sqlQuery = "SELECT * FROM {$wpdb->prefix}picking_pedidos 
                WHERE Fecha_salida BETWEEN '{$initialDate}' AND '{$finalDate}'";
    
            $rta = $wpdb->get_results($sqlQuery, ARRAY_A);
    
            $serverResponse = $rta;
     
        }

    } else {

        return new WP_Error( 'Unauthorized ', __('Acceso denegado'), array( 'status' => 401 ) );

        $serverResponse = array(
            "msg" => "Sin autorizaci贸n",
        );


    };

    return $serverResponse;


}

//Endpoint para obtener los productos de un pedido
function getProductosFromPedido($data){

    global $acceptedToken;

    $recivedToken = $_SERVER['HTTP_TOKEN'];

    $serverResponse = array(
        "msg" => "Error al obtener los productos",
    );

    $idPedido = $data['idpedido'];

    if ($acceptedToken == $recivedToken){
    
            global $wpdb;
    
            $sqlQuery = "SELECT * FROM {$wpdb->prefix}productos_en_pedidos WHERE idPedido LIKE '{$idPedido}'";
    
            $rta = $wpdb->get_results($sqlQuery, ARRAY_A);
    
            $serverResponse = $rta;
     

    } else {

        return new WP_Error( 'Unauthorized ', __('Acceso denegado'), array( 'status' => 401 ) );

        $serverResponse = array(
            "msg" => "Sin autorizaci贸n",
        );


    };

    return $serverResponse;


}



add_action('admin_enqueue_scripts', 'cargarJSfromBootstrap');

add_action('admin_enqueue_scripts', 'cargarCSSfromBootstrap');

add_action('admin_enqueue_scripts', 'cargarMyJs');

add_action('admin_enqueue_scripts', 'cargarTablaPedidosJs');

add_action('admin_enqueue_scripts', 'cargarMyCss');

add_action('init','triggerDownloadTable');

//Register API

add_action('rest_api_init', function () {
    register_rest_route('pedidosplugin/v1', '/insertpedidos', array(
        'methods' => 'POST',
        'callback' => 'addModifyPedidosEndPoint',
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('pedidosplugin/v1', '/getpedidosdespachados', array(
        'methods' => 'GET',
        'callback' => 'getPedidosDespachados',
        'permission_callback' => '__return_true',
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('pedidosplugin/v1', '/getproductosfrompedido', array(
        'methods' => 'POST',
        'callback' => 'getProductosFromPedido',
        'permission_callback' => '__return_true',
    ));
});
