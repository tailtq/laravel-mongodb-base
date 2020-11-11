if (typeof Dropzone !== 'undefined') {
  Dropzone.autoDiscover = false;
}

function initWizardForProcess() {
  if (!$.fn.steps) return;

  const validRequiredInputs = ($section) => {
    let valid = true;

    const $inputs = $section.find('input[required]');

    $inputs.each((_, input) => {
      if (!$(input).val().trim()) {
        valid = false;
        if ($(input).parent().hasClass('input-group')) {
          $(input).parent().parent().append(`
            <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này`
          );
        } else {
          $(input).parent().append(`
            <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này`
          );
        }

      } else {
        $(input).next('label').remove();
      }
    });

    return valid;
  };

  const serializeObject = (serializeArray) => {
    const result = {};

    serializeArray.forEach((element) => {
      result[element.name] = element.value
    });

    return result;
  };

  const $processForm = $('#process-form');

  $processForm.steps({
    headerTag: 'h2',
    bodyTag: 'section',
    transitionEffect: 'slideLeft',
    onStepChanging: function (event, currentIndex) {
      return validRequiredInputs($($processForm.find('section')[currentIndex]));
    },
    onFinishing: function (event, currentIndex) {
      return validRequiredInputs($($processForm.find('section')[currentIndex]));
    },
    onFinished: function (event, currentIndex) {
      $('#process-form a[href="#finish"]').attr('disabled', true);
      const serializableData = $processForm.serializeArray();

      if (serializableData.length === 0 || !$processForm.hasClass('editable')) {
        return true;
      }

      $.ajax({
        url: '/processes/create',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify({
          _token: $('meta[name="_token"]').attr('content'),
          ...serializeObject(serializableData)
        }),
        success: function (res) {
          window.location.href = `/processes/${res.data.id}`;
        },
        error: function (res) {
          $('#process-form a[href="#finish"]').attr('disabled', false);

          Swal.fire({
            icon: 'error',
            title: 'Đã có lỗi xảy ra',
            text: res.responseJSON.message,
          });
        }
      });
    }
  });
}

function initDropzone() {
  if (!$.fn.dropzone || typeof Dropzone == 'undefined') return;

  const dropzoneVideo = new Dropzone('.dropzone', {
    url: '/medias',
    paramName: 'files',
    uploadMultiple: false,
    autoProcessQueue: false,
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
    },
    init: function () {
      this.on('success', function(res) {
        const body = JSON.parse(res.xhr.response);
        $('.dropzone-field').val(body.data[0]);
      });
    },
  });

  $('.dropzone-submit').click(function (e) {
    e.preventDefault();
    dropzoneVideo.processQueue();
  });

  $('a[href="#collapseDropzone"]').click(function () {
    const $field = $('.dropzone-field');
    $field.attr('readonly', !$field.attr('readonly'));
  });
}

$(document).ready(function () {
  initWizardForProcess();
  initDropzone();
});
