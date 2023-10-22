var minL;
    var maxL;
function paramPass(minl , maxl) {
    minL = minl;
    maxL = maxl;
}

$(document).ready(function() {
    $('#ncontra').keyup(function() {
        
        $('#strengthMessage').html(checkStrength($('#ncontra').val()));
    });
    

    function checkStrength(password) {
        var strength = 0;
        if (password.length < minL) {
            $('#strengthMessage').removeClass();
            $('#strengthMessage').addClass('Short, text-danger');
            mensajeAutoERROR('Error de Contraseña', 'La contraseña es demasiado corta y no cumple con los requerimientos de seguriad');
            return 'Demasiado corto min ' + minL;
        }

        // If password contains both lower and uppercase characters, increase strength value.
        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            // If it has numbers and characters, increase strength value.
        if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1;
            // If it has one special character, increase strength value.
        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~,-])/)) strength += 1;
            // If it has two special characters, increase strength value.

        // Calculated strength value, we can return messages
        // If value is less than 2
        if (strength < 2) {
            $('#strengthMessage').removeClass();
            $('#strengthMessage').addClass('Weak');
            return 'Débil';
        } else if (strength === 2) {
            $('#strengthMessage').removeClass();
            $('#strengthMessage').addClass('Weak');
            return 'Bueno';
        } else {
            $('#strengthMessage').removeClass();
            $('#strengthMessage').addClass('Weak');
            $('#fuerte').val('1');
            return 'Fuerte';
        }
    }
});

function CheckUserName(ele) {
    if (/\s/.test(ele.value)) {
        $.notify(datos, 'success');
        ("no se permiten espacios en blanco");
    }
}

function soloLetras(e) {
    key = e.keyCode || e.which;
    tecla = String.fromCharCode(key).toLowerCase();
    letras = " áéíóúabcdefghijklmnñopqrstuvwxyz";
    especiales = "8-37-39-46";
    tecla_especial = false;
    for (var i in especiales) {
        if (key == especiales[i]) {
            tecla_especial = true;
            break;
        }
    }
    if (letras.indexOf(tecla) == -1 && !tecla_especial) {
        return false;
    }
}

function aaa(campo, event) {
    CadenaaReemplazar = " ";
    CadenaReemplazo = "";
    CadenaTexto = campo.value;
    CadenaTextoNueva = CadenaTexto.split(CadenaaReemplazar).join(CadenaReemplazo);
    campo.value = CadenaTextoNueva;
}
window.onload = function() {
    var myInput = document.getElementById('ncontra');
    myInput.onpaste = function(e) {
        e.preventDefault();
    }
    myInput.oncopy = function(e) {
        e.preventDefault();
    }
}

function mostrarContrasena() {
    var tipo = document.getElementById("ncontra");
    if (tipo.type == "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }
}

function soloLetras(e) {
    key = e.keyCode || e.which;
    tecla = String.fromCharCode(key).toLowerCase();
    letras = " áéíóúabcdefghijklmnñopqrstuvwxyz";
    especiales = "8-37-39-46";
    tecla_especial = false
    for (var i in especiales) {
        if (key === especiales[i]) {
            tecla_especial = true;
            break;
        }
    }
    if (letras.indexOf(tecla) == -1 && !tecla_especial) {
        return false;
    }
}

function mostrarContrasena1() {
    var tipo = document.getElementById("ncontra");
    if (tipo.type === "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }
}

function mostrarContrasena() {
    var tipo = document.getElementById("rcontra");
    if (tipo.type == "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }

}

function mostrarContrasena3() {
    var tipo = document.getElementById("Contraseñaa");
    if (tipo.type === "password") {
        tipo.type = "text";
    } else {
        tipo.type = "password";
    }

}