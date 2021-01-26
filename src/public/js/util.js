function renderIdentityName(identityName, confidenceRate) {
    if (!identityName) {
        return null;
    }
    if (!confidenceRate) {
        return identityName;
    }
    return `${identityName} (${confidenceRate}%)`;
}

function buildTimeRanges(appearances) {
    let timeRanges = '';

    appearances.forEach((value) => {
        const clusteringType = `<p class="text-center">Id: ${value.track_id} ${value.clustering_type ? ` - ${value.clustering_type}` : ''}</p>`;
        let imageHTML = '';
        let bodyHTML = '';
        let time = '';

        if (isRealtime) {
            const timeFrom = new Date(value.time_from);
            const timeTo = new Date(value.time_from);
            time += `${addZero(timeFrom.getHours())}:${addZero(timeFrom.getMinutes())}:${addZero(timeFrom.getSeconds())}`;
            time += ` - ${addZero(timeTo.getHours())}:${addZero(timeTo.getMinutes())}:${addZero(timeTo.getSeconds())}`;
        } else {
            time = getTimeString(value.frame_from, value.frame_to, fps, renderHour);
        }

        value.images = JSON.parse(value.images);
        value.body_images = value.body_images ? JSON.parse(value.body_images) : [];

        value.images.forEach((image) => {
            imageHTML += `<img class="original-avatar" src="${image}" alt="">`;
        });
        value.body_images.forEach((body) => {
            bodyHTML += `<img class="original-body" src="${body}" alt="">`;
        });
        // TODO: Generate HTML and CSS content
        timeRanges += `
            <button class="badge badge-info"
                    role="button"
                    data-html="true"
                    data-toggle="popover"
                    data-placement="top"
                    data-id="${value.id}"
                    data-track-id="${value.track_id}"
                    data-identity-id="${value.identity_id}"
                    data-cluster-id="${value.cluster_id}"
                    data-mongo-id="${value.mongo_id}"
                    data-content='<div>${clusteringType}<div class="text-center">${imageHTML}</div><div class="text-center">${bodyHTML}</div></div>'
                    data-trigger="focus">${time}</button> &nbsp;
        `;
    });

    return timeRanges;
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

function reloadIcons() {
    feather.replace();
    $('[data-toggle="popover"]').popover();
}
