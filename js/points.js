//check what is the page name, is it index.php or results.php
let Cpage = window.location.pathname.split("/").pop();

//variable to store user actions log
let userActions = [];

//specific for index.php and qualification.php
if (Cpage === "index.php" || Cpage === "" || Cpage === "qualification.php" || Cpage === "userLog.php") {

    const originalCanvasSize = 833;
    let scaledCanvasSize; // this changes when the window is resized
    //set size on first load
    document.addEventListener('DOMContentLoaded', () => {
        scaledCanvasSize = canvas.clientWidth;
    });
    //handle the resize
    window.addEventListener('resize', () => {
        //get the new size of the canvas
        scaledCanvasSize = canvas.clientWidth;
    });

    //utility functions for canvas points
    function drawPolygon() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw lines connecting the polygon points
        ctx.beginPath();
        ctx.moveTo(polygonPoints[0].x, polygonPoints[0].y);
        for (let i = 1; i < polygonPoints.length; i++) {
            ctx.lineTo(polygonPoints[i].x, polygonPoints[i].y);
        }
        ctx.closePath();
        ctx.stroke();

        // Draw the polygon points
        for (let i = 0; i < polygonPoints.length; i++) {
            const point = polygonPoints[i];
            ctx.beginPath();
            ctx.arc(point.x, point.y, pointRadius, 0, 2 * Math.PI);
            ctx.fillStyle = i === selectedPointIndex ? 'yellow' : '#888'; // Highlight the selected point in red
            ctx.fill();
            ctx.stroke();
        }
    }

    function highlightPoint(point) {
        ctx.beginPath();
        ctx.arc(point.x, point.y, pointRadius + 1, 0, 2 * Math.PI);
        ctx.fillStyle = '#bdbcff';
        ctx.fill();
    }

    function unhighlightPoints() {
        updateCanvas();
    }

    // Call this function to redraw the canvas whenever needed
    function updateCanvas() {
        drawPolygon();
    }

    // Function to handle mouse down event
    function onMouseDown(event) {
        const mouseX = event.offsetX * (originalCanvasSize / scaledCanvasSize);
        const mouseY = event.offsetY * (originalCanvasSize / scaledCanvasSize);

        // Check if the mouse is down on any polygon point
        for (let i = 0; i < polygonPoints.length; i++) {
            const point = polygonPoints[i];
            const distance = Math.sqrt((point.x - mouseX) ** 2 + (point.y - mouseY) ** 2);

            if (distance <= pointRadius) {
                selectedPointIndex = i;
                canvas.addEventListener('mousemove', onMouseMove);
                //update the user actions log
                //to avoid unnecessary entries, we check if the last entry in the log has same action, pointIndex and coordinates
                //if so, we do not add a new entry
                if (userActions[canvas.id.slice(-1)]["log"].length > 0) {
                    const lastEntry = userActions[canvas.id.slice(-1)]["log"][userActions[canvas.id.slice(-1)]["log"].length - 1];
                    if (lastEntry.action !== "select" || lastEntry.pointIndex !== selectedPointIndex || lastEntry.coordinates.x !== mouseX || lastEntry.coordinates.y !== mouseY) {
                        userActions[canvas.id.slice(-1)]["log"].push({
                            action: "select",
                            pointIndex: selectedPointIndex,
                            coordinates: {
                                x: mouseX,
                                y: mouseY
                            },
                            timestamp: Date.now()
                        });
                    }
                } else {
                    userActions[canvas.id.slice(-1)]["log"].push({
                        action: "select",
                        pointIndex: selectedPointIndex,
                        coordinates: {
                            x: mouseX,
                            y: mouseY
                        },
                        timestamp: Date.now()
                    });
                }
                break;
            }
        }

        // create a new point on the line (if applicable)
        for (let i = 0; i < polygonPoints.length; i++) {
            const nextIndex = (i + 1) % polygonPoints.length;
            const point1 = polygonPoints[i];
            const point2 = polygonPoints[nextIndex];
            const distanceToLine = distanceToLineSegment(mouseX, mouseY, point1.x, point1.y, point2.x, point2.y);
            //if close enough to the line, but not too close to the points, add a new point
            if (distanceToLine <= pointRadius) { // line condition
                //now check both points to make sure the new point is not too close to them
                const distanceToPoint1 = Math.sqrt((point1.x - mouseX) ** 2 + (point1.y - mouseY) ** 2);
                const distanceToPoint2 = Math.sqrt((point2.x - mouseX) ** 2 + (point2.y - mouseY) ** 2);
                if (distanceToPoint1 > pointRadius + 1 && distanceToPoint2 > pointRadius + 1) {
                    canvas.style.cursor = 'copy';
                    polygonPoints.splice(nextIndex, 0, {
                        x: mouseX,
                        y: mouseY
                    });
                    selectedPointIndex = nextIndex;
                    canvas.addEventListener('mousemove', onMouseMove);
                    //update the user actions log
                    userActions[canvas.id.slice(-1)]["log"].push({
                        action: "create",
                        pointIndex: selectedPointIndex,
                        coordinates: {
                            x: mouseX,
                            y: mouseY
                        },
                        timestamp: Date.now()
                    });
                    break;
                }
            }
        }
        updateCanvas();
    }


    // Function to handle mouse move event while dragging a point
    function onMouseMove(event) {
        const mouseX = event.offsetX * (originalCanvasSize / scaledCanvasSize);
        const mouseY = event.offsetY * (originalCanvasSize / scaledCanvasSize);
        polygonPoints[selectedPointIndex].x = mouseX;
        polygonPoints[selectedPointIndex].y = mouseY;
        updateCanvas();
    }

    //function to highlight the points on hover
    function onMouseHover(event) {
        const mouseX = event.offsetX * (originalCanvasSize / scaledCanvasSize);
        const mouseY = event.offsetY * (originalCanvasSize / scaledCanvasSize);

        // Check if the mouse is down on any polygon point
        for (let i = 0; i < polygonPoints.length; i++) {
            const point = polygonPoints[i];
            const distance = Math.sqrt((point.x - mouseX) ** 2 + (point.y - mouseY) ** 2);

            if (distance <= pointRadius) {
                canvas.style.cursor = 'grab';
                highlightPoint(point);
                break;
            } else {
                canvas.style.cursor = 'default';
                unhighlightPoints();
            }
        }

        for (let i = 0; i < polygonPoints.length; i++) {
            const nextIndex = (i + 1) % polygonPoints.length;
            const point1 = polygonPoints[i];
            const point2 = polygonPoints[nextIndex];
            const distanceToLine = distanceToLineSegment(mouseX, mouseY, point1.x, point1.y, point2.x, point2.y);
            //if close enough to the line, but not too close to the points, add a new point
            if (distanceToLine <= pointRadius) { // line condition
                //now check both points to make sure the new point is not too close to them
                const distanceToPoint1 = Math.sqrt((point1.x - mouseX) ** 2 + (point1.y - mouseY) ** 2);
                const distanceToPoint2 = Math.sqrt((point2.x - mouseX) ** 2 + (point2.y - mouseY) ** 2);
                if (distanceToPoint1 > pointRadius + 1 && distanceToPoint2 > pointRadius + 1) {
                    canvas.style.cursor = 'copy';
                    break;
                }
            }
        }
    }

    // Function to handle mouse up event
    function onMouseUp(event) {
        const mouseX = event.offsetX * (originalCanvasSize / scaledCanvasSize);
        const mouseY = event.offsetY * (originalCanvasSize / scaledCanvasSize);
        canvas.removeEventListener('mousemove', onMouseMove);
        if (Cpage !== "qualification.php") {
            canvas.parentElement.querySelector('.nextBtn').disabled = false;
        }
        //update the user actions log
        //mouse up is tricky because it always adds an entry to the log, even if point is not moved, but rather selected/created
        //to avoid unnecessary entries, we compare the event coordinates with the last entry in the log and only add a new entry if they are different
        //we also check the pointIndex of the current entry and do not add anything if the pointIndex is -1 (no point selected)
        if (userActions[canvas.id.slice(-1)]["log"].length > 0) {
            const lastEntry = userActions[canvas.id.slice(-1)]["log"][userActions[canvas.id.slice(-1)]["log"].length - 1];
            if (lastEntry.pointIndex !== -1 && (lastEntry.coordinates.x !== mouseX || lastEntry.coordinates.y !== mouseY)) {
                userActions[canvas.id.slice(-1)]["log"].push({
                    action: "move",
                    pointIndex: selectedPointIndex,
                    coordinates: {
                        x: mouseX,
                        y: mouseY
                    },
                    timestamp: Date.now()
                });
            }
        }
    }

    // Function to handle click event on the delete button
    function onDeleteButtonClick() {
        //delete the selected point if there are more than 3 points
        if (selectedPointIndex !== -1) {
            if (polygonPoints.length > 3) {
                //update the user actions log
                userActions[canvas.id.slice(-1)]["log"].push({
                    action: "delete",
                    pointIndex: selectedPointIndex,
                    coordinates: {
                        x: polygonPoints[selectedPointIndex].x,
                        y: polygonPoints[selectedPointIndex].y
                    },
                    timestamp: Date.now()
                });
                polygonPoints.splice(selectedPointIndex, 1);
                selectedPointIndex = -1;
                updateCanvas();
            } else {
                alert("A polygon must have at least 3 points!");
            }
        }
    }

    // Add event listeners to the canvas
    function interactiveCanvas(canvas) {
        canvas.addEventListener('mousemove', onMouseHover);
        canvas.addEventListener('mousedown', onMouseDown);
        canvas.addEventListener('mouseup', onMouseUp);
    }

    // Add event listener to delete the selected point when delete is pressed on the keyboard
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Delete') {
            onDeleteButtonClick();
        }
    });

    // Utility function to calculate the distance from a point to a line segment
    function distanceToLineSegment(x, y, x1, y1, x2, y2) {
        const A = x - x1;
        const B = y - y1;
        const C = x2 - x1;
        const D = y2 - y1;

        const dot = A * C + B * D;
        const len_sq = C * C + D * D;
        let param = -1;

        if (len_sq !== 0) // Avoid division by 0
            param = dot / len_sq;

        let xx, yy;

        if (param < 0) {
            xx = x1;
            yy = y1;
        } else if (param > 1) {
            xx = x2;
            yy = y2;
        } else {
            xx = x1 + param * C;
            yy = y1 + param * D;
        }

        const dx = x - xx;
        const dy = y - yy;
        return Math.sqrt(dx * dx + dy * dy);
    }
}