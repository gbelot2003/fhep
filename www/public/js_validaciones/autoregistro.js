var tabla;

//Función que se ejecuta al inicio
function init() {


    $("#formularioregistros").on("submit", function(e) {
        guardaryeditar(e);
    })
}


//Función para guardar o editar

function guardaryeditar(e) {
    e.preventDefault(); //No se activará la acción predeterminada del evento
    $("#btnGuardar").prop("disabled", true);
    var formData = new FormData($("#formulario")[0]);

    $.ajax({
        url: "../ajax/autoregistro.php?op=guardaryeditar",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,

        success: function(datos) {
            $.notify(datos, 'success');
            (datos);
            window.location.href = '../form/index.html';


        }

    });

}



init();