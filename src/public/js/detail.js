let currentFrame = 0;
let trackIds = [];

// define global variables at detail.blade.php
// const processId = '{{ $process->id }}';
// const allStatus = <?= json_encode(__('status', [], 'vi'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
// const frameDrop = {{ object_get($process->mongoData, 'frame_drop', 1) }};
// const totalFrames = Math.round(parseInt({{ $process->total_frames }}, 10) / frameDrop);
// const fps = Math.round(parseInt('{{ $process->fps }}', 10) / frameDrop);
// const renderHour = totalFrames / fps / 3600 >= 1;
// const renderHour = false;
// let globalStatus = '{{ $process->status }}';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
});

// function for alert message when click action play, stop
function processMessage(type) {
    if (type === 'start') {
        Toast.fire({
            type: 'success',
            title: 'Bắt đầu thực thi'
        });
    } else {
        Toast.fire({
            type: 'success',
            title: 'Kết thúc thực thi'
        });
    }
}

function sendStartStopRequest(processId, type) {
    $.ajax({
        url: `/processes/${type}-process`,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify({
            _token: $('meta[name="_token"]').attr('content'),
            processId: processId
        }),
        success: function (res) {
            if (type === 'start') {
                $('.btn-start').attr('disabled', true);
                $('.btn-stop').attr('disabled', false);
            } else if (type === 'stop') {
                $('.btn-stop').attr('disabled', true);
            }
            processMessage(type);
        },
        error: function ({responseJSON: res}) {
            Toast.fire({
                type: 'error',
                title: res.message
            });
        }
    });
}

$('.btn-start').click(function () {
    sendStartStopRequest(processId, 'start');
});

$('.btn-stop').click(function () {
    sendStartStopRequest(processId, 'stop');
});

function buildProgressBar(times, totalFrames, fps, renderHour, shouldIncreasing = false) {
    let bars = ``;
    let currentTime = 0;

    times.forEach(({frame_from: frameFrom, frame_to: frameTo}) => {
        const length = frameTo - frameFrom;
        const transparentLength = frameFrom - currentTime;

        // for shouldIncreasing case, we just render a red slice of the bar
        bars += `
            <div class="progress-bar bg-transparent" role="progressbar"
                 data-toggle="tooltip"
                 style="width: ${transparentLength / totalFrames * 100}%"></div>
    
            <div class="progress-bar progress-bar-striped ${shouldIncreasing ? 'bg-danger' : 'bg-success'}" role="progressbar"
                 data-frame-from="${frameFrom}"
                 style="width: ${shouldIncreasing ? 1 : (length / totalFrames * 100)}%"
                 data-toggle="popover"
                 data-placement="bottom"
                 data-trigger="hover"
                 data-content="${getTimeString(frameFrom, frameTo, fps, renderHour)}"></div>`;

        currentTime += frameTo;
    });

    return `<div class="progress ht-15">${bars}</div>`;
}

function getTimeString(frameFrom, frameTo, fps, renderHour) {
    let secondFrom = Math.floor(frameFrom / fps);
    let minFrom = Math.floor(secondFrom / 60);
    const hourFrom = (Math.floor(minFrom / 60)).toString().padStart(2, '0');
    minFrom = (minFrom % 60).toString().padStart(2, '0');
    secondFrom = (secondFrom % 60).toString().padStart(2, '0');

    if (!Number.isInteger(frameTo)) {
        return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - now`;
    }

    let secondTo = Math.floor(frameTo / fps);
    let minTo = Math.floor(secondTo / 60);
    const hourTo = (Math.floor(minTo / 60)).toString().padStart(2, '0');
    minTo = (minTo % 60).toString().padStart(2, '0');
    secondTo = (secondTo % 60).toString().padStart(2, '0');

    return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - ${renderHour ? `${hourTo}:` : ''}${minTo}:${secondTo}`;
}

function getLightboxBlock(images, id) {
    images = images ? JSON.parse(images) : null;

    return images ? `
                <a href="${images[0].url}" data-lightbox="object-${id}">
                    <img src="${images[0].url}" style="width: inherit; height: 60px;" alt="">
                </a>
            ` : ``;
}

function renderBlock(object, appearances, fps, renderHour, shouldIncreasing) {
    return (`
        <tr data-track-id="${object.track_id}" data-id="${object.id}" data-identity-id="${object.identity_id}"
            ${!object.identity_id && $('[name="hide-unknown"]').is(':checked') ? 'style="display: none"' : ''}>
            <td class="text-center">${object.track_id}</td>
            <td class="text-center">
                <img src="${object.image}" alt="image"
                     style="width: inherit; height: 60px;">
            </td>
            <td class="text-center">${getLightboxBlock(object.images, object.id)}</td>
            <td>${object.name || 'Không xác định'}</td>
            <td class="position-relative">
                ${buildProgressBar(appearances, totalFrames, fps, renderHour, shouldIncreasing)}
                <div class="position-absolute status-overlay ${shouldIncreasing ? 'increasing' : ''}">
                    ${shouldIncreasing ? 'Đang nhận diện' : ''}
                </div>
            </td>
            <td width="50px" class="text-center">
                <a href="#"
                   data-video-result="${object.video_result || ''}"
                   style="display: ${globalStatus === 'done' ? 'inline' : 'none'}"
                   class="render-single-object icon__normal-font-size ${object.video_result ? 'text-success' : 'text-secondary'}">
                    ${object.video_result ? `<i class="mdi mdi-play"></i>` : '<i class="mdi mdi-video-switch"></i>'}
                </a>
            </td>
        </tr>
    `);
}

function renderBlockInOrder(html, order, trackIds) {
    let index;

    if (order !== 0) {
        index = order - 1;
    }
    if (order === 0) {
        $('.socket-render tbody').prepend(html);
    } else {
        const prevTrackId = trackIds[index];
        const $element = $(`.socket-render tbody tr[data-track-id="${prevTrackId}"]`);

        if ($element.length === 0) {
            $('.socket-render tbody').append(html);
        } else {
            $element.after(html);
        }
    }
}

function insertInOrder(element, array) {
    array.push(element);
    array.sort(function (a, b) {
        return a - b;
    });

    return [array, array.indexOf(element)];
}

function renderData() {
    $.ajax({
        url: `/processes/${processId}/objects`,
        type: 'GET',
        success: function (res) {
            if (res.data.length > 0) {
                $('.socket__message').remove();
            }

            res.data.forEach((value) => {
                if (trackIds.indexOf(value.track_id) >= 0) {
                    return;
                }
                [trackIds, trackIndex] = insertInOrder(value.track_id, trackIds);

                renderBlockInOrder(
                    renderBlock(value, value.appearances, fps, renderHour, !Number.isInteger(value.appearances[0].frame_to)),
                    trackIndex,
                    trackIds
                );
            });
            feather.replace();
            $('[data-toggle="popover"]').popover();
        },
    });
}

function renderTime() {
    $.ajax({
        url: `/processes/${processId}/detail`,
        type: 'GET',
        success: function (res) {
            const {
                detecting_duration: detectingDuration,
                matching_duration: matchingDuration,
                rendering_duration: renderingDuration,
                total_duration: totalDuration,
            } = res.data;

            $('.process__detecting-duration').html(detectingDuration);
            $('.process__matching-duration').html(matchingDuration);
            $('.process__rendering-duration').html(renderingDuration);
            $('.process__total-duration').html(totalDuration);
        },
    });
}

Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectsAppear', (res) => {
    $('.socket__message').remove();
    console.log(res);

    res.data.forEach((value) => {
        if (trackIds.indexOf(value.track_id) >= 0) {
            $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(5)`).html(`
                ${buildProgressBar([value], totalFrames, fps, renderHour, false)}
                <div class="position-absolute status-overlay"></div>
            `);
            if (value.name) {
                $(`.socket-render tbody tr[data-track-id="${value.track_id}"]`).attr('data-identity-id', value.identity_id).removeAttr('style');
                $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(2)`).html(`
                    <img src="${value.image}" alt="image" style="width: inherit; height: 60px;">
                `);
                $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(3)`).html(getLightboxBlock(value.images, value.id));
                $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(4)`).text(value.name);
                $(`.socket-render tbody tr[data-track-id="${value.track_id}"] td:nth-child(6)`).html(`
                    <a href="#"
                       data-video-result=""
                       style="display: ${globalStatus === 'done' ? 'inline' : 'none'}"
                       class="render-single-object icon__normal-font-size text-secondary">
                        <i class="mdi mdi-video-switch"></i>
                    </a>
                `);
            }
        } else {
            [trackIds, trackIndex] = insertInOrder(value.track_id, trackIds);

            renderBlockInOrder(
                renderBlock(value, [value], fps, renderHour, true),
                trackIndex,
                trackIds
            );
        }
    });

    $('.process__ungrouped-count').html(trackIds.length);
    feather.replace();
    $('[data-toggle="popover"]').popover();
});

Echo.channel(`process.${processId}.progress`).listen('.App\\Events\\ProgressChange', (res) => {
    console.log('Socket data', res.data);

    const {
        status,
        progress,
        total,
        video_result: videoResult,
        frame_index: frameIndex,
    } = res.data;

    globalStatus = status;

    if (!isNaN(frameIndex)) {
        currentFrame = frameIndex;
    }
    if (allStatus[status]) {
        const $processStatus = $('.process__status');
        $processStatus.text(allStatus[status]);

        if (status === 'error' || status === 'stopped') {
            $processStatus.removeClass('badge-success').addClass('badge-danger');
        }
    }
    if (status === 'done' && videoResult) {
        Toast.fire({
            type: 'success',
            title: 'Tiến trình đã hoàn thành',
        });

        $('.video-rendering__btn').html(`
            <a class="btn btn-primary" target="_blank" href="${videoResult}">Video tái hiện</a>
        `);
        $('.search-face__btn').removeAttr('disabled');
        $('.render-single-object').removeAttr('style');
        renderTime();
    } else if (status === 'detecting' || status === 'rendering') {
        const $element = $(`.progress-bar__${status}`);

        $element.css({width: `${progress}%`});
        $element.attr('aria-valuenow', progress);
        $element.text(`${progress}%`);
    } else if (status === 'matching') {
        const $element = $(`.progress-bar__${status}`);
        const trackLength = trackIds.length;
        const percentage = trackLength > 0 ? total / trackLength * 100 : 0;

        $element.css({width: `${percentage}%`});
        $element.attr('aria-valuenow', progress);
        $element.text(`(${total}/${trackLength}) ${parseInt(percentage, 10)}%`);
    } else if (status === 'grouping') {
        Toast.fire({
            type: 'success',
            title: 'Đang nhất thể hoá',
        });
    } else if (status === 'grouped') {
        Toast.fire({
            type: 'success',
            title: 'Nhất thể hoá thành công',
        });

        $.ajax({
            url: `/processes/${processId}/objects`,
            type: 'GET',
            success: function (res) {
                res.data.forEach((value) => {
                    $(`.socket-render tbody tr[data-id="${value.id}"] td:nth-child(5)`).html(`
                        ${buildProgressBar(value.appearances, totalFrames, fps, renderHour, false)}
                        <div class="position-absolute status-overlay"></div>
                    `);

                    value.appearances.forEach((appearance) => {
                        if (appearance.object_id !== appearance.old_object_id) {
                            $(`.socket-render tbody tr[data-id="${appearance.old_object_id}"]`).fadeOut(3000);
                        }
                    });
                });
                $('.process__grouped-count').html(res.data.length);
                $('.process__identified-count').html(res.data.filter(e => !!e.identity_id).length);
                $('.process__unidentified-count').html(res.data.filter(e => !e.identity_id).length);
                $('[data-toggle="popover"]').popover();
            },
        });
    }
});

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

Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectVideoRendered', function (res) {
    const { data } = res;

    $(`.socket-render tbody tr[data-id="${data.id}"] td:last-child a`)
        .html('<i class="mdi mdi-play"></i>')
        .addClass('text-success')
        .removeClass('disabled')
        .blur()
        .data('video-result', data.video_result);
    $(`.search-face__result li[data-id="${data.id}"] a.render-single-object`)
        .html('<i class="mdi mdi-play"></i>')
        .addClass('text-success')
        .removeClass('disabled')
        .blur()
        .data('video-result', data.video_result);
    feather.replace();
});

$('[name="hide-unknown"]').on('change', function () {
    const shouldHide = $(this).is(':checked');
    const $null = $(`.socket-render tbody tr[data-identity-id="null"]`);
    const $undefined = $(`.socket-render tbody tr[data-identity-id="undefined"]`);

    if (shouldHide) {
        $null.fadeOut(1000);
        $undefined.fadeOut(1000);
    } else {
        $null.fadeIn(1000);
        $undefined.fadeIn(1000);
    }
});

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
            formData.append('process_ids', JSON.stringify([$('.process').data('id')]));
        },
        success: function (res) {
            const { data } = JSON.parse(res.xhr.response);

            $('.search-face__result').removeAttr('style');
            $('.search-face__result .list-unstyled').html('');

            if (data.length === 0) {
                $('.search-face__result .list-unstyled').html(`<li>Không có đối tượng nào được tìm thấy</li>`);
                return;
            }
            data.forEach((element) => {
                let appearance = '';
                element.appearances.forEach((element) => {
                    appearance += `<span class="badge badge-primary mr-1">${getTimeString(element.frame_from, element.frame_to, fps, renderHour)}</span>`;
                });

                $('.search-face__result .list-unstyled').append(`
                    <li class="media d-block d-sm-flex mb-3 align-items-center" data-id="${element.id}">
                        <img src="${element.image}" class="mb-3 mb-sm-0 mr-3 img-fluid" style="height: 80px;">

                        <div class="media-body">
                            <p class="mt-0 mb-1"><b>${element.name || 'Không xác định'}</b></p>
                            <div>
                                <p>Thời gian xuất hiện:</p>
                                ${appearance}
                            </div>
                        </div>
                        
                        <div>
                            <a href="#"
                               data-video-result="${element.video_result || ''}"
                               class="render-single-object icon__normal-font-size ${element.video_result ? 'text-success' : 'text-secondary'}">
                                ${element.video_result ? `<i class="mdi mdi-play"></i>` : '<i class="mdi mdi-video-switch"></i>'}
                            </a>
                        </div>
                    </li>      
                `);
            });
        },
        complete: function () {
            $('.dropzone-submit').attr('disabled', false).text('Tìm kiếm');
        },
    });

    $('.dropzone-submit').click(function (e) {
        e.preventDefault();
        dropzone.processQueue();
    });
}

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

$(document).ready(function () {
    renderData();
    listenObjectRenderingEvent();
    initSearchFace();
});
