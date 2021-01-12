let regions = [];
let regionIndex = 0;
let movingIndexes = null;
let drawingType = null;

// init when calling loadCanvas function
let canvas;
let ctx;
let offsetX;
let offsetY;
let isDrawing = true;
const sizes = {
    resized: {},
};
// end

const radius = 4;
const colors = {
    detecting: 'rgb(102, 209, 209, 0.6)',
    tracking: 'rgba(151, 35, 44, 0.6)',
};

// follow geojson format
function initNewRegion(trackingColor, detectingColor) {
    return {
        tracking_region: {
            color: trackingColor,
            type: 'Polygon',
            coordinates: [[]],
        },
        detecting_region: {
            color: detectingColor,
            type: 'Polygon',
            coordinates: [[]],
        },
    };
}

// re-render regions
function loadOldRegions() {
    let regions = $('input[name="regions"]').val();

    if (!!regions) {
        regions = JSON.parse(regions);

        regions.forEach((region) => {
            Object.keys(region).forEach((key) => {
                region[key].color = colors[key.replace('_region', '')];

                region[key].coordinates[0].forEach((coordinate, coordinateKey) => {
                    region[key].coordinates[0][coordinateKey] = new Circle(
                        parseInt(coordinate[0] * canvas.width),
                        parseInt(coordinate[1] * canvas.height),
                        radius,
                        region[key].color,
                    );
                });
            });
        });
    }

    return regions || [];
}

function loadCanvas() {
    const imgElement = $('#canvas-img');
    const canvasElement = $('#canvas');

    canvas = canvasElement[0];
    canvas.width = imgElement[0].width;
    canvas.height = imgElement[0].height;

    ctx = canvas.getContext('2d');
    const BB = canvas.getBoundingClientRect();
    offsetX = BB.left;
    offsetY = BB.top;

    sizes.resized.width = imgElement[0].width;
    sizes.resized.height = imgElement[0].height;

    initCanvasEvents(canvasElement);
    regions = loadOldRegions();

    if (regions.length > 0) {
        drawPolygons(regions, null, ctx);
    } else {
        regions.push(initNewRegion(colors.tracking, colors.detecting));
    }
}

function clearCanvas(canvasWidth, canvasHeight, ctx) {
    ctx.clearRect(0, 0, canvasWidth, canvasHeight);
}

function drawPolygons(regions, drawingType, ctx) {
    regions.forEach((region) => {
        // sort to choose which key will be draw over which key
        const allKeys = Object.keys(region).sort((a, b) => (
            (a === drawingType) - (b === drawingType)
        ));

        // draw by all keys
        allKeys.forEach((key) => {
            const { coordinates, color } = region[key];

            if (coordinates[0].length === 0) {
                return;
            }
            // draw coordinates
            coordinates[0].forEach((coordinate) => {
                coordinate.draw(ctx);
            });
            // draw polygon
            ctx.beginPath();
            ctx.moveTo(coordinates[0].x, coordinates[0].y);
            coordinates[0].forEach((coordinate) => {
                ctx.lineTo(coordinate.x, coordinate.y);
            });
            ctx.fillStyle = color;
            ctx.strokeStyle = color;
            ctx.fill();
            ctx.stroke();
        });
    });
}

function saveRegions(regions, resizedSize) {
    let newRegions = JSON.parse(JSON.stringify(regions));
    newRegions = newRegions.map((region) => {
        Object.keys(region).forEach((key) => {
            delete region[key].color;
            // normalize data
            region[key].coordinates[0] = region[key].coordinates[0].map((coordinate) => {
                coordinate.x /= resizedSize.width;
                coordinate.y /= resizedSize.height;

                return [coordinate.x, coordinate.y];
            });
        });

        return region;
    });
    $(`input[name="regions"]`).val(JSON.stringify(newRegions));
}

// get index for dragging
function getDraggedIndex(regions, mouseX, mouseY) {
    let regionIndex = null;
    let coordinateIndex = null;
    let drawingType = null;
    let isFound = false;

    regions.forEach((region, regionI) => {
        if (isFound) {
            return;
        }
        Object.keys(region).forEach((key) => {
            if (isFound) {
                return;
            }
            region[key].coordinates[0].forEach((coordinate, coordinateI) => {
                if (isFound) {
                    return;
                }
                const xRange = [coordinate.x - coordinate.radius, coordinate.x + coordinate.radius];
                const yRange = [coordinate.y - coordinate.radius, coordinate.y + coordinate.radius];

                if ((mouseX >= xRange[0] && mouseX <= xRange[1]) && (mouseY >= yRange[0] && mouseY <= yRange[1])) {
                    regionIndex = regionI;
                    coordinateIndex = coordinateI;
                    drawingType = key;
                    isFound = true;
                }
            });
        });
    });
    if (regionIndex === null) {
        return null;
    }

    return {regionIndex, coordinateIndex, drawingType};
}

function getMouseCoordinate(e) {
    const mouseX = parseInt(e.clientX - offsetX);
    const mouseY = parseInt(e.clientY - offsetY);

    return {mouseX, mouseY};
}

function removeActive() {
    $(`.canvas-toolbox button`).removeClass('active');
}

function initCanvasEvents(canvasElement) {
    // drag coordinate
    canvasElement.mousemove(function (e) {
        if (movingIndexes === null) {
            return;
        }
        const {regionIndex, drawingType, coordinateIndex} = movingIndexes;

        e.preventDefault();
        e.stopPropagation();

        const {mouseX, mouseY} = getMouseCoordinate(e);
        regions[regionIndex][drawingType].coordinates[0][coordinateIndex].drag(mouseX, mouseY);

        clearCanvas(canvas.width, canvas.height, ctx);
        drawPolygons(regions, drawingType, ctx);
        saveRegions(regions, sizes.resized);
    });

    // check dragging or insert new point
    canvasElement.mousedown(function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!isDrawing) {
            return;
        }

        const {mouseX, mouseY} = getMouseCoordinate(e);
        const indexes = getDraggedIndex(regions, mouseX, mouseY);

        // remove particular coordinate
        if (drawingType === 'delete') {
            if (indexes) {
                const {regionIndex, drawingType, coordinateIndex} = indexes;
                regions[regionIndex][drawingType].coordinates[0].splice(coordinateIndex, 1);

                clearCanvas(canvas.width, canvas.height, ctx);
                drawPolygons(regions, drawingType, ctx);
                saveRegions(regions, sizes.resized);
            }
        } if (drawingType && (indexes === null || indexes.drawingType !== drawingType)) {
            regions[regionIndex][drawingType].coordinates[0].push(new Circle(
                mouseX,
                mouseY,
                radius,
                regions[regionIndex][drawingType].color,
            ));

            clearCanvas(canvas.width, canvas.height, ctx);
            drawPolygons(regions, drawingType, ctx);
            saveRegions(regions, sizes.resized);
        } else {
            movingIndexes = indexes;
        }
    });

    canvasElement.mouseup(function () {
        movingIndexes = null;
    });

    $('#canvas__detecting').on('click', function () {
        removeActive();
        drawingType = 'detecting_region';
        $(this).addClass('active');
    });
    
    $('#canvas__tracking').on('click', function () {
        removeActive();
        drawingType = 'tracking_region';
        $(this).addClass('active');
    });
    
    $('#canvas__finish').on('click', function () {
        removeActive();
        drawingType = null;
    });

    $('#canvas__delete').on('click', function () {
        removeActive();
        drawingType = 'delete';
        $(this).addClass('active');
    });
}
