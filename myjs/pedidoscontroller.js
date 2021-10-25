jQuery(document).ready(function($){

    $("#btnnuevo").click(function(){

        console.log('click nuevo')

    });

    $('.numeroPedido').focus();



});

function playAlert(condition) {

    if (condition == 'success'){
        var audio = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');
        audio.play();

    } else if (condition == 'repeated'){

        var audio = new Audio('https://actions.google.com/sounds/v1/emergency/emergency_siren_short_burst.ogg');
        audio.play();

    } else if(condition == 'notexists'){

        var audio = new Audio('https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg');
        audio.play();

    }

}