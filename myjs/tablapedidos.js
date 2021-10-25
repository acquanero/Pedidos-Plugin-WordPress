async function getProductos(id) {

    var myHeaders = new Headers();
    myHeaders.append("token", "9245fe4a-d402-451c-b9ed-9c1a04247482");
    myHeaders.append("Content-Type", "application/json");

    var raw = JSON.stringify({
        "idpedido": id
    });

    var requestOptions = {
        method: 'POST',
        headers: myHeaders,
        body: raw,
        redirect: 'follow'
    };

    //Descomentar para las pruebas locales, y comentar la otra linea
    //const rta = await fetch(window.location.protocol + '//' + window.location.hostname+"/wordpress/wp-json/pedidosplugin/v1/getproductosfrompedido", requestOptions);

    const rta = await fetch(window.location.protocol + '//' + window.location.hostname+"/wp-json/pedidosplugin/v1/getproductosfrompedido", requestOptions);
        // .then(response => response.text())
        // .then(result => console.log(result))
        // .catch(error => console.log('error', error));
    return rta.json();
}

jQuery(document).ready(function($){

    let buttonCerrar = document.getElementById("cerrarProductos");

    let bodyTabla = document.getElementById("bodyTablaProductos");

    let popUpProductos = document.getElementById("popUpProductos");

    let idPedidoHyperLinkList = document.getElementsByClassName("clickableIdPedido");

    for (let n = 0; n < idPedidoHyperLinkList.length; n++){

        idPedidoHyperLinkList[n].onclick = async (e) => {
            event.preventDefault();
            while (bodyTabla.firstChild) {
                bodyTabla.removeChild(bodyTabla.lastChild);
              }

            let id = idPedidoHyperLinkList[n].textContent;

            let arrayProductos = await getProductos(id);

            popUpProductos.style.display = "block";

            for(let i=0; i < arrayProductos.length; i++){

                let hilera = document.createElement("tr");

                let celdaUno = document.createElement("td");
                let textoCeldaUno = document.createTextNode(arrayProductos[i].idPedido);

                let celdaDos = document.createElement("td");
                let textoCeldaDos = document.createTextNode(arrayProductos[i].sku);

                let celdaTres = document.createElement("td");
                let textoCeldaTres = document.createTextNode(arrayProductos[i].descripcion);
                
                let celdaCuatro = document.createElement("td");
                let textoCeldaCuatro = document.createTextNode(arrayProductos[i].cantidadProductos);
    
                celdaUno.appendChild(textoCeldaUno);
                celdaDos.appendChild(textoCeldaDos);
                celdaTres.appendChild(textoCeldaTres);
                celdaCuatro.appendChild(textoCeldaCuatro);

                hilera.appendChild(celdaUno);
                hilera.appendChild(celdaDos);
                hilera.appendChild(celdaTres);
                hilera.appendChild(celdaCuatro);
                bodyTabla.appendChild(hilera);
            }
            return false;
        }
    }

    buttonCerrar.onclick = function () {
        popUpProductos.style.display = "none";
    }


});
