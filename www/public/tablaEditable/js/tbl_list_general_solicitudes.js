/**
 * highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
var urlData = "";
var urlBase = "";

function direcciones(urldata, urlbase) {
    urlData = urldata;
    urlBase = urlbase;
}

function highlightRow(rowId, bgColor, after) {
    var rowSelector = $("#" + rowId);
    rowSelector.css("background-color", bgColor);
    rowSelector.fadeTo("normal", 0.5, function () {
        rowSelector.fadeTo("fast", 1, function () {
            rowSelector.css("background-color", "");
        });
    });
}

function highlight(div_id, style) {
    highlightRow(div_id, style === "error" ? mensajeUpdateERROR('Error!', 'Campo No pudo ser actualizado') : style === "warning" ? mensajeUpdadeWARNING('Advertencia!', 'Campo NO Actualizado') : mensajeUpdateSUCCESS('Acualización Exitosa!', 'Campo Actualizado'));
}

/**
 * updateCellValue calls the PHP script that will update the database.
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse) {
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
        success: function (response) {
            if (response !== 'error' && response !== 'ok') {
                mensajeERROR('Error!', response);
                editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
            } else {

                // reset old value if failed then highlight row
                var success = onResponse ? onResponse(response) : (response === "ok" || !isNaN(parseInt(response))); // by default, a successful response can be "ok" or a database id 
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
    grid.setCellRenderer("opciones", new CellRenderer({
        render: function (cell, id) {
            // Code for rendering the action column
            // ... (Your existing code for rendering the action column)
        }
    }));
    // fin render for the action column

    grid.renderGrid("divtabla", "table table-hover table-responsive table-bordered  table-striped", "tablas");
    //Volver a colocar el valor que está usado como filtro
    $("#filter").val(grid.currentFilter);
};

function updatePaginator(grid, divId) {
    // ... (Your existing code for updating the paginator)
}
