

function mensajeERROR(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: true, time: 2000, class_name: "gritter-error"});
}

function mensajeAutoERROR(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 4000, class_name: "gritter-error"});
}

function mensajeUpdateERROR(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 2000, class_name: "gritter-error"});
}

function mensajeAutoSUCCESS(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 5000, class_name: "gritter-success"});
}

function mensajeUpdateSUCCESS(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 2000, class_name: "gritter-success"});
}

function mensajeAutoINFO(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 2000, class_name: "gritter-info"});
}

function mensajeINFO(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: true, time: 5000, class_name: "gritter-info"});
}

function mensajeAutoWARNING(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 5000, class_name: "gritter-warning"});
}

function mensajeWARNING(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: true, time: 5000, class_name: "gritter-warning"});
}

function mensajeUpdadeWARNING(titulo, texto) {
    $.gritter.add({title: titulo, text: texto, sticky: false, time: 2000, class_name: "gritter-warning"});
}
