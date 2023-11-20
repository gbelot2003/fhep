/**
 *  highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
var urlData = "";
var urlBase = "";
function direcciones(urldata, urlbase) {
    urlData = urldata;
    urlBase = urlbase;
}

function highlightRow(rowId, bgColor, after)
{

    var rowSelector = $("#" + rowId);
    rowSelector.css("background-color:" + bgColor);
    rowSelector.fadeTo("normal", 0.5, function () {
        rowSelector.fadeTo("fast", 1, function () {
            rowSelector.css("background-color", '');
        });
    });
}

function highlight(div_id, style) {


    highlightRow(div_id, style === "error" ? mensajeUpdateERROR('Error!', 'Campo No pudo ser actualizado') : style === "warning" ? mensajeUpdadeWARNING('Advertencia!', 'Campo NO Actualizado') : mensajeUpdateSUCCESS('Acualización Exitosa!', 'Campo Actualizado'));
}

/**
 updateCellValue calls the PHP script that will update the database. 
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{
    $.ajax({
        url: urlUpdate,
        type: 'POST',
        dataType: "html",
        data: {
            nombreTabla: editableGrid.name,
            id: editableGrid.getRowId(rowIndex),
            nuevoValor: editableGrid.getColumnType(columnIndex) === "boolean" ? (newValue ? 1 : 0) : newValue,
            nombreCampo: editableGrid.getColumnName(columnIndex),
            tipoColumna: editableGrid.getColumnType(columnIndex)
        },
        success: function (response)
        {
            if (response !== 'error' && response !== 'ok') {
                mensajeERROR('Error!', response);
                editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
            } else {

                // reset old value if failed then highlight row
                var success = onResponse ? onResponse(response) : (response === "ok" || !isNaN(parseInt(response))); // by default, a sucessfull reponse can be "ok" or a database id 
                if (!success)
                    editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
                highlight(row.id, success ? "ok" : "error");
            }
        },
        error: function (XMLHttpRequest, textStatus, exception) {
            alert("Ajax failure\n" + errortext);
        },
        async: true
    });
}



function DatabaseGrid(tabla) {


    this.editableGrid = new EditableGrid(tabla, {
        enableSort: true,
        doubleclick: true,
        ignoreLastRow: false,
        // define the number of row visible by page
        pageSize: 10,
        // Once the table is displayed, we update the paginator state
        tableRendered: function () {

            updatePaginator(this);
        },
        tableLoaded: function () {

            datagrid.initializeGrid(this);
        },
        modelChanged: function (rowIndex, columnIndex, oldValue, newValue, row) {
            updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
        }
    });
    this.fetchGrid();
}

DatabaseGrid.prototype.fetchGrid = function () {
// Llamar al action para obtener los datos.

    this.editableGrid.loadXML(urlData);
};
DatabaseGrid.prototype.initializeGrid = function (grid) {

    var self = this;
// render for the action column  
//    grid.setCellRenderer("opciones", new CellRenderer({
//        render: function (cell, id) {
//
////            var cod = grid.getValueAt(cell.rowIndex, 0);
////            var identidad = grid.getValueAt(cell.rowIndex, 1);
////            var estado = grid.getValueAt(cell.rowIndex, 3);
//
//            var menu = '';
//            menu += '';
//            menu += '';
//            menu += '<div class=" " id="navbarsExample05" style="text-align: right;">';
//            menu += '<button class="nav-link mx-auto dropdown-toggle button  btn btn-sm bg-primary text-white" href="#" data-bs-toggle="dropdown">';
//            menu += 'Opciones</button>';
//            menu += '<ul class="dropdown-menu " >';
//            menu += '<li><a href="' + urlBase + '/pacientes/preclinica/'+ id + '" class="btn btn-sm dropdown-item" ><span class="fas fa-file-signature"></span> Preclinear</a></li>';
//
//            menu += '<li><a href="' + urlBase + '/pacientes/activar/' + id + '" class="btn btn-sm dropdown-item" ><span class="	fas fa-user-edit"></span> Cambiar estado</a></li>';
//            menu += '<li><a href="' + urlBase + '/pacientes/inactivar/' + id + '" class="btn btn-sm dropdown-item" ><span class="fas fa-eye"></span> Ver</a></li>';
//
//
//            menu += '<li class="divider"></li>';
//            menu += '</ul>';
//            menu += '</div>';
//            menu += '';
//            menu += '';
//            menu += '';
//            menu += '';
//
//
//            cell.innerHTML += menu;
//
//        }
//    }));
    // fin render for the action column  


    grid.renderGrid("divtabla", "table table-hover table-responsive table-bordered  table-striped", "tablas");
    //Volver a colocar el valor que esta usado como filtro
    $("#filter").val(grid.currentFilter);
};

function updatePaginator(grid, divId) {


    divId = divId || "paginator";
    var paginator = $("#" + divId).empty();
    var nbPages = grid.getPageCount();
    // get interval
    var interval = grid.getSlidingPageInterval(5);
    if (interval === null)
        return;
    // get pages in interval (with links except for the current page)
    var pages = grid.getPagesInInterval(interval, function (pageIndex, isCurrent) {
        if (isCurrent)
            return "<span id='currentpageindex'>" + (pageIndex + 1) + "</span>";
        return $("<a>").css("cursor", "pointer").html(pageIndex + 1).click(function (event) {
            grid.setPageIndex(parseInt($(this).html()) - 1);
        });
    });
    // "first" link
    var link = $("<a class='nobg' title='Ir al primero'>").html("<i class=' fa fa-angle-left'><i class=' fa fa-angle-left'> ");
    if (!grid.canGoBack())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function (event) {
            grid.firstPage();
        });
    paginator.append(link);
    // "prev" link
    link = $("<a class='nobg' title='Ir al anterior'>").html("<i class=' fa fa-angle-left'> ");
    if (!grid.canGoBack())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function (event) {
            grid.prevPage();
        });
    paginator.append(link);
    // pages
    for (p = 0; p < pages.length; p++)
        paginator.append(pages[p]).append(" ");
    // "next" link
    link = $("<a class='nobg' title='Ir al siguiente'>").html(" <i class=' fa fa-angle-right'>");
    if (!grid.canGoForward())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function (event) {
            grid.nextPage();
        });
    paginator.append(link);
    // "last" link
    link = $("<a class='nobg' title='Ir al último'>").html(" <i class=' fa fa-angle-right'><i class=' fa fa-angle-right'>");
    if (!grid.canGoForward())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function (event) {
            grid.lastPage();
        });
    paginator.append(link);
}
;




