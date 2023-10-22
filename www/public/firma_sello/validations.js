$(document).ready(() => {
  function cargarImagen(selector) {
    $(selector + " .file").change((e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.addEventListener("load", (e) => {
          $(selector + " .photo").attr("src", e.target.result);
        });
        reader.readAsDataURL(file);
        if (!$(selector + " .preview").is(":visible")) {
          $(selector + " .preview").show();
        }
      }
    });
  }

  function quitarImagen(selector) {
    $(selector + " .close-icon").click(() => {
      $(selector + " .preview").hide();
      $(selector + " .file").val("");
      $('#firma-sello').trigger("change");
    });
  }

  function guardarImagen() {
    $("#firma-sello").submit((e) => {
      e.preventDefault();
      e.stopPropagation();
  
      const form = e.currentTarget;
      const url = new URL(form.action);
      const formData = new FormData(form);
  
      $.ajax({
        url: url.pathname,
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        cache: false,
        success: () => {
          $(".firma .close-icon").trigger('click');
          $(".sello .close-icon").trigger('click');
        },
        error: (e) => {
          console.log('TENEMOS UN ERROR!', e);
          // $("#err").html(e).fadeIn();
        },
      });
    });
  }

  function actualizarFormulario() {
    $("#firma-sello").change((e) => {
      const form = e.currentTarget;
      const formData = new FormData(form);
      if (formData.get('firma').size > 0 && formData.get('sello').size > 0) {
        $('.btn-submit').show();
      } else {
        $('.btn-submit').hide();
      }
  
    });
  }

  cargarImagen(".firma");
  cargarImagen(".sello");
  quitarImagen(".firma");
  quitarImagen(".sello");
  guardarImagen();
  actualizarFormulario();
});
