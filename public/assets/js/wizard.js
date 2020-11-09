const validRequiredInputs = ($section) => {
  let valid = true;

  const $inputs = $section.find('input[required]');

  $inputs.each((_, input) => {
    if (!$(input).val().trim()) {
      valid = false;
      $(input).parent().append(`
        <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này`
      );
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

$(function () {
  'use strict';

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

      $.ajax({
        url: '/processes/create',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify({
          _token: $('meta[name="_token"]').attr('content'),
          ...serializeObject($processForm.serializeArray())
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

  $('#wizard').steps({
    headerTag: 'h2',
    bodyTag: 'section',
    transitionEffect: 'slideLeft'
  });

  $('#wizardVertical').steps({
    headerTag: 'h2',
    bodyTag: 'section',
    transitionEffect: 'slideLeft',
    stepsOrientation: 'vertical'
  });
});