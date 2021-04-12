Dropzone.autoDiscover = false;


function addSpinningIcon(self, isBtn) {
    if (isBtn) {
        $(self).attr('disabled', true);
    } else {
        $(self).addClass('disabled');
    }

    $(self).html(`<i class="icon__normal-size" data-feather="rotate-cw"></i>`);
    feather.replace();
    $(self).find('svg').addClass('infinite-spin');
}

function handleSearch(res) {
    $('.dropzone-submit').attr('disabled', false).text('Tìm kiếm');
    const { data } = res;

    $('.search-face__result').removeAttr('style');
    $('.search-face__result .list-unstyled').html('');

    if (data.length === 0) {
        $('.search-face__result .list-unstyled').html(`<li>Không có đối tượng nào được tìm thấy</li>`);
        return;
    }
    data.forEach((element) => {
        $('.search-face__result .list-unstyled').append(`
            <li class="media d-block d-sm-flex mb-3 align-items-center" data-id="${element._id}">
                <img src="${element.avatars[0]}" class="mb-3 mb-sm-0 mr-3 img-fluid" style="height: 80px;">

                <div class="media-body">
                    <p class="mt-0 mb-1">
                        <b>
                            ${renderIdentityName(element.identity, element.confidence_rate)}
                        </b>
                    </p>
                    <div>
                        <p>Thời gian xuất hiện:</p>
                        ${buildTimeRanges(element.appearances)}
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <a href="#"
                       data-video-result="${element.video_result || ''}"
                       class="render-single-object icon__normal-font-size ${element.video_result ? 'text-success' : 'text-secondary'}">
                        ${element.video_result ? `<i class="mdi mdi-play"></i>` : '<i class="mdi mdi-video-switch"></i>'}
                    </a>
                    <a href="#" class="icon__normal-font-size text-success search-body__btn">
                        <i class="mdi mdi-account-search"></i>
                    </a>
                </div>
            </li>
        `);
    });
    reloadIcons();
}

function initSearchFace() {
    if (!$.fn.dropzone || typeof Dropzone == 'undefined') return;

    const dropzone = new Dropzone('.search-face__dropzone', {
        url: '/processes/search-faces',
        paramName: 'file',
        uploadMultiple: false,
        autoProcessQueue: false,
        maxFilesize: 3000,
        timeout: 360000,
        addRemoveLinks: true,
        dictRemoveFile: 'Xóa hình',
        dictCancelUpload: 'Huỷ',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
        },
        sending: function (file, xhr, formData) {
            addSpinningIcon($('.dropzone-submit'), true);
            formData.append('search_type', $('[name="search_type"]:checked').val());

            const processId = $('.process').data('id');
            if (processId) {
                formData.append('process_ids', JSON.stringify([processId]));
            }
        },
        success: function (res) {
            handleSearch(JSON.parse(res.xhr.response));
        },
        error: function () {
            $('.dropzone-submit').attr('disabled', false).text('Tìm kiếm');
        },
    });

    $('.dropzone-submit').click(function (e) {
        e.preventDefault();
        dropzone.processQueue();
    });
}

function listenSearchBodyAfterImageSearch() {
    $(document).on('click', '.search-body__btn', function (e) {
        e.preventDefault();
        const id = $(this).parent().closest('li').data('id');
        const processId = $('.process').data('id') || undefined;
        addSpinningIcon($('.dropzone-submit'), true);

        $.ajax({
            url: '/processes/search-faces',
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('meta[name="_token"]').attr('content'),
                object_id: id,
                search_type: 'body',
                process_id: processId,
            },
            success: function (res) {
                handleSearch(res);
            }
        });
    });
}

function listenObjectRenderingEvent() {
    $(document).on('click', '.render-single-object', function (e) {
        e.preventDefault();
        const videoResult = $(this).data('video-result');

        if (videoResult) {
            $('#videoModal').modal('show');
            $('#videoModal video').attr('src', videoResult);
            return;
        }
        const id = $(this).parent().closest('tr').data('id') || $(this).parent().closest('li').data('id');

        $(this).addClass('disabled');
        $(this).html(`<i class="icon__normal-size" data-feather="rotate-cw"></i>`);
        feather.replace();
        $(this).find('svg').addClass('infinite-spin');

        $.post(`/objects/${id}/rendering`, {
            _token: $('meta[name="_token"]').attr('content'),
        }).then((res) => {
            // TODO: add spinning icon
            console.log(res);
        });
    });
}

$(document).ready(function () {
    initSearchFace();
    listenSearchBodyAfterImageSearch();
    listenObjectRenderingEvent();
});
