//check what is the page name, is it index.php or results.php
let page = window.location.pathname.split("/").pop();;

//in both cases:
//update the year in the footer
document.getElementById("year").innerHTML = new Date().getFullYear();

function detectmobile() {
    var a = navigator.userAgent || navigator.vendor || window.opera;
    if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) {
        return true;
    } else {
        return false;
    }
}

//if the user is on a mobile device, we show a warning instead of the task
if (detectmobile()) {
    document.querySelector("body").innerHTML = "<h2>⚠️Sorry!</h2><p style='text-align: center; padding: 0 10px;'>This task is not available on mobile devices. Please use a desktop computer or laptop.<br><br>💻🖥️</p>";
}

//specific for index.php and results.php
if (page === "index.php" || page === "" || page === 'qualification.php' || page === 'userLog.php') {
    // variables and dom elements
    const values = {};
    const confirmBtn = document.getElementById("confirmBtn");

    //show first task
    document.querySelector(".task-wrapper").classList.remove("hidden");

    //controls dropdown
    const controls = document.getElementById("controls_wrapper");
    const controls_title = document.getElementById("controls_title");
    controls_title.addEventListener("click", () => {
        controls_title.classList.toggle("open");
        controls.classList.toggle("hidden");
    });

    //specific for index.php and results.php
    if (page === "index.php" || page === "" || page === 'userLog.php') {
        //make tasks interactive
        let tasks = document.querySelectorAll(".task-wrapper");
        tasks.forEach(task => {
            //prepare the output object
            values[task.id] = {};
            //each task has a submit button with class nextBtn
            let nextBtn = task.querySelector(".nextBtn");
            nextBtn.disabled = true;
            //

            //when the user clicks the button, the next task is shown
            nextBtn.addEventListener("click", () => {
                //first check for self intersection
                //add the first point at the end to close the polygon
                polygonPoints.push(polygonPoints[0]);
                //convert to turf polygon
                let userPolygon = turf.polygon([polygonPoints.map(point => turf.toWgs84([point.x, point.y]))]);
                //check if user polygon is valid
                let kinks = turf.kinks(userPolygon);
                if (kinks.features.length > 1) {
                    swal({
                        title: "Oops!",
                        text: "Your polygon is self-intersecting! Please improve your polygon and try again.",
                        icon: "warning",
                    });
                    //we also want to pop the points added to the groundTruth and polygonPoints to close them
                    polygonPoints.pop();
                    return;
                }

                //save the results from the current task
                values[task.id] = polygonPoints;
                console.log(values);

                //log the current userActions to the console
                console.log('userActions:');
                console.log(userActions);
                console.log('##########################');

                //hide the current task
                task.classList.add("hidden");
                //show the next task if there is one
                if (task.nextElementSibling && task.nextElementSibling.classList.contains("task-wrapper")) {
                    task.nextElementSibling.classList.remove("hidden");
                    //scroll to the next task
                    task.nextElementSibling.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                        inline: "nearest",
                    });

                    let currentTask = task.nextElementSibling.querySelector("canvas").id.slice(-1);
                    polygonPoints = polygonPoints_[currentTask];
                    canvas = canvas_[currentTask];
                    ctx = ctx_[currentTask];
                    selectedPointIndex = selectedPointIndex_[currentTask];
                    pointRadius = pointRadius_[currentTask];
                    interactiveCanvas(canvas);
                    drawPolygon();
                } else {
                    //show confirm button
                    confirmBtn.classList.remove("hidden");
                }
            });
        });

        //when confirmed the values are sent to the server = saveResults.php
        //if it returns the success message, the user is redirected to the results page = results.php
        if (confirmBtn) {
            confirmBtn.addEventListener("click", async () => {
                //first collect the date from all the inputs and store it in the values object
                console.log(values);
                tasks.forEach(task => {
                    console.log(task);
                });
                //send data as json 
                let data = JSON.stringify({
                    userInfo: userInfo,
                    dataInfo: dataInfo,
                    values: values,
                    userLog: userActions
                });
                let response = await fetch("saveResults.php", {
                    method: "POST",
                    body: data,
                    headers: {
                        "Content-Type": "application/json",
                    },
                });
                let result = await response.text();
                console.log(result);
                if (result == "success") {
                    window.location.href = "results.php?vcode=" + userInfo.vcode;
                }
            });
        }
    } else { //qualification.php
        //adjust the header text
        document.querySelector("header span").innerText = "Qualification Task";
        //GROUND TRUTH to compare against
        const groundTruths = [
            [{
                "x": 420.00390625,
                "y": 509.4453125
            }, {
                "x": 421.00390625,
                "y": 491.4453125
            }, {
                "x": 442.00390625,
                "y": 497.4453125
            }, {
                "x": 459.00390625,
                "y": 497.4453125
            }, {
                "x": 469.00390625,
                "y": 518.4453125
            }, {
                "x": 466.00390625,
                "y": 552.4453125
            }, {
                "x": 477.00390625,
                "y": 579.4453125
            }, {
                "x": 492.00390625,
                "y": 592.4453125
            }, {
                "x": 505.00390625,
                "y": 578.4453125
            }, {
                "x": 519.00390625,
                "y": 584.4453125
            }, {
                "x": 532.00390625,
                "y": 573.4453125
            }, {
                "x": 545.00390625,
                "y": 547.4453125
            }, {
                "x": 550.00390625,
                "y": 553.4453125
            }, {
                "x": 584.00390625,
                "y": 548.4453125
            }, {
                "x": 610.00390625,
                "y": 546.4453125
            }, {
                "x": 615.00390625,
                "y": 513.4453125
            }, {
                "x": 585.00390625,
                "y": 497.4453125
            }, {
                "x": 576.00390625,
                "y": 481.4453125
            }, {
                "x": 600.00390625,
                "y": 471.4453125
            }, {
                "x": 604.00390625,
                "y": 451.4453125
            }, {
                "x": 582.00390625,
                "y": 427.4453125
            }, {
                "x": 564.00390625,
                "y": 413.4453125
            }, {
                "x": 549.00390625,
                "y": 392.4453125
            }, {
                "x": 534.00390625,
                "y": 367.4453125
            }, {
                "x": 530.00390625,
                "y": 356.4453125
            }, {
                "x": 529.00390625,
                "y": 336.4453125
            }, {
                "x": 500.00390625,
                "y": 317.4453125
            }, {
                "x": 490.00390625,
                "y": 306.4453125
            }, {
                "x": 484.00390625,
                "y": 278.4453125
            }, {
                "x": 471.00390625,
                "y": 259.4453125
            }, {
                "x": 461.00390625,
                "y": 249.4453125
            }, {
                "x": 430.00390625,
                "y": 261.4453125
            }, {
                "x": 409.00390625,
                "y": 253.4453125
            }, {
                "x": 381.00390625,
                "y": 242.4453125
            }, {
                "x": 359.00390625,
                "y": 218.4453125
            }, {
                "x": 334.00390625,
                "y": 209.4453125
            }, {
                "x": 304.00390625,
                "y": 232.4453125
            }, {
                "x": 286.00390625,
                "y": 277.4453125
            }, {
                "x": 299.00390625,
                "y": 306.4453125
            }, {
                "x": 312.00390625,
                "y": 329.4453125
            }, {
                "x": 312.00390625,
                "y": 348.4453125
            }, {
                "x": 295.00390625,
                "y": 373.4453125
            }, {
                "x": 284.00390625,
                "y": 397.4453125
            }, {
                "x": 265.00390625,
                "y": 408.4453125
            }, {
                "x": 250.00390625,
                "y": 423.4453125
            }, {
                "x": 232.00390625,
                "y": 434.4453125
            }, {
                "x": 214.00390625,
                "y": 431.4453125
            }, {
                "x": 217.00390625,
                "y": 467.4453125
            }, {
                "x": 236.00390625,
                "y": 477.4453125
            }, {
                "x": 257.00390625,
                "y": 487.4453125
            }, {
                "x": 246.00390625,
                "y": 509.4453125
            }, {
                "x": 242.00390625,
                "y": 527.4453125
            }, {
                "x": 267.00390625,
                "y": 541.4453125
            }, {
                "x": 301.00390625,
                "y": 554.4453125
            }, {
                "x": 320.00390625,
                "y": 552.4453125
            }, {
                "x": 329.00390625,
                "y": 523.4453125
            }, {
                "x": 346.00390625,
                "y": 526.4453125
            }, {
                "x": 351.00390625,
                "y": 541.4453125
            }, {
                "x": 360.00390625,
                "y": 553.4453125
            }, {
                "x": 379.00390625,
                "y": 564.4453125
            }, {
                "x": 397.00390625,
                "y": 567.4453125
            }, {
                "x": 424.00390625,
                "y": 552.4453125
            }, {
                "x": 437.00390625,
                "y": 538.4453125
            }, {
                "x": 441.00390625,
                "y": 524.4453125
            }],
            [{
                "x": 340,
                "y": 289.05
            }, {
                "x": 306,
                "y": 305.05
            }, {
                "x": 261,
                "y": 340.05
            }, {
                "x": 250,
                "y": 392.05
            }, {
                "x": 250,
                "y": 436.05
            }, {
                "x": 270,
                "y": 458.05
            }, {
                "x": 293,
                "y": 492.05
            }, {
                "x": 326,
                "y": 503.05
            }, {
                "x": 290,
                "y": 515.05
            }, {
                "x": 346,
                "y": 549.05
            }, {
                "x": 378,
                "y": 555.05
            }, {
                "x": 398,
                "y": 538.05
            }, {
                "x": 423,
                "y": 539.05
            }, {
                "x": 425,
                "y": 556.05
            }, {
                "x": 444,
                "y": 553.05
            }, {
                "x": 464,
                "y": 561.05
            }, {
                "x": 474,
                "y": 532.05
            }, {
                "x": 502,
                "y": 549.05
            }, {
                "x": 522,
                "y": 561.05
            }, {
                "x": 552,
                "y": 568.05
            }, {
                "x": 561,
                "y": 538.05
            }, {
                "x": 554,
                "y": 505.05
            }, {
                "x": 577,
                "y": 462.05
            }, {
                "x": 558,
                "y": 446.05
            }, {
                "x": 553,
                "y": 371.05
            }, {
                "x": 574,
                "y": 345.05
            }, {
                "x": 523,
                "y": 296.05
            }, {
                "x": 492,
                "y": 248.05
            }, {
                "x": 471,
                "y": 273.05
            }, {
                "x": 414,
                "y": 274.05
            }, {
                "x": 341,
                "y": 288.05
            }, {
                "x": 338,
                "y": 290.05
            }],
            [{
                "x": 329,
                "y": 469
            }, {
                "x": 342,
                "y": 442
            }, {
                "x": 323,
                "y": 418
            }, {
                "x": 323,
                "y": 379
            }, {
                "x": 331,
                "y": 335
            }, {
                "x": 370,
                "y": 296
            }, {
                "x": 399,
                "y": 308
            }, {
                "x": 429,
                "y": 293
            }, {
                "x": 470,
                "y": 316
            }, {
                "x": 511,
                "y": 330
            }, {
                "x": 537,
                "y": 356
            }, {
                "x": 555,
                "y": 404
            }, {
                "x": 549,
                "y": 449
            }, {
                "x": 527,
                "y": 482
            }, {
                "x": 495,
                "y": 510
            }, {
                "x": 460,
                "y": 528
            }, {
                "x": 420,
                "y": 530
            }, {
                "x": 392,
                "y": 524
            }, {
                "x": 379,
                "y": 492
            }, {
                "x": 367,
                "y": 515
            }, {
                "x": 339,
                "y": 508
            }, {
                "x": 329,
                "y": 469
            }, {
                "x": 329,
                "y": 469
            }, {
                "x": 329,
                "y": 469
            }]
        ]

        let groundTruth = groundTruths[randomDataIndex];

        //when the user submits we compare the submitted polygon with the ground truth
        if (confirmBtn) {
            confirmBtn.addEventListener("click", () => {
                //compare the submitted polygon with the ground truth
                console.log(groundTruth);
                console.log(polygonPoints);

                //convert to turf.js format (also add the first point at the end to close the polygon)
                groundTruth.push(groundTruth[0]);
                polygonPoints.push(polygonPoints[0]);
                //convert to wgs84 to get the correct area
                let userPolygon = turf.polygon([polygonPoints.map(point => turf.toWgs84([point.x, point.y]))]);
                let GTPolygon = turf.polygon([groundTruth.map(point => turf.toWgs84([point.x, point.y]))]);

                //check if user polygon is valid
                let kinks = turf.kinks(userPolygon);
                if (kinks.features.length > 1) {
                    swal({
                        title: "Oops!",
                        text: "Your polygon is self-intersecting! Please improve your polygon and try again.",
                        icon: "warning",
                    });
                    //we also want to pop the points added to the groundTruth and polygonPoints to close them
                    groundTruth.pop();
                    polygonPoints.pop();
                    return;
                }

                //get the intersection
                let intersection = turf.intersect(userPolygon, GTPolygon);
                //if the user polygon is not intersecting with the ground truth, we show a warning and stop the function
                if (!intersection) {
                    swal({
                        title: "Qualification failed!",
                        text: "Your selection is not good enough! Please improve your polygon so it matches the tree and try again.",
                        icon: "warning",
                    });
                    //we also want to pop the points added to the groundTruth and polygonPoints to close them
                    groundTruth.pop();
                    polygonPoints.pop();
                    return;
                }
                //get the union
                let union = turf.union(userPolygon, GTPolygon);
                //get areas of all polygons
                let userArea = turf.area(userPolygon);
                let GTArea = turf.area(GTPolygon);
                let intersectionArea = turf.area(intersection);
                let unionArea = turf.area(union);

                // //show the intersection and the union on the canvas
                // //convert back from wgs84 to the canvas coordinates
                // intersection = turf.toMercator(intersection);
                // union = turf.toMercator(union);
                // ctx.clearRect(0, 0, canvas.width, canvas.height);
                // ctx.fillStyle = "rgba(255, 0, 0, 0.5)";
                // ctx.beginPath();
                // intersection.geometry.coordinates[0].forEach((point, index) => {
                //     if (index === 0) {
                //         ctx.moveTo(point[0], point[1]);
                //     } else {
                //         ctx.lineTo(point[0], point[1]);
                //     }
                // });
                // ctx.closePath();
                // ctx.fill();

                // ctx.fillStyle = "rgba(0, 0, 255, 0.2)";
                // ctx.beginPath();
                // union.geometry.coordinates[0].forEach((point, index) => {
                //     if (index === 0) {
                //         ctx.moveTo(point[0], point[1]);
                //     } else {
                //         ctx.lineTo(point[0], point[1]);
                //     }
                // });
                // ctx.closePath();
                // ctx.fill();

                console.log('%cuser Area', 'color:blue', userArea.toLocaleString('de-DE', {
                    minimumFractionDigits: 2
                }));
                console.log('%cGT Area', 'color:black', GTArea.toLocaleString('de-DE', {
                    minimumFractionDigits: 2
                }));
                console.log('%cintersection Area', 'color:red', intersectionArea.toLocaleString('de-DE', {
                    minimumFractionDigits: 2
                }));
                console.log('%cunion Area', 'color:green', unionArea.toLocaleString('de-DE', {
                    minimumFractionDigits: 2
                }));
                //get iou
                let iou = intersectionArea / unionArea;

                console.log('%cIoU', 'color:orange', (100 * iou).toFixed(2));

                if (iou >= 0.85) {
                    //if the sequence is correct, we allow the user to go to the main task
                    document.querySelector('.task-wrapper').innerHTML = "<h2>Correct!</h2><p>You will now be redirected to the main task.</p><div class='loader'><i class='fas fa-spinner'></i></div>";
                    localStorage.setItem(`ifp_crowdsourcing_qualification_${userInfo.campaign}`, 1);
                    setTimeout(() => {
                        window.location.href = "index.php?campaign=" + userInfo.campaign + "&worker=" + userInfo.worker + "&rand_key=" + userInfo.random;
                    }, 3000);
                } else {
                    //otherwise, we show a warning telling the user to try again
                    swal({
                        title: "Qualification failed!",
                        text: "Your selection is not good enough! Please improve your polygon so it matches the tree and try again.",
                        icon: "warning",
                    });
                    //we also want to pop the points added to the groundTruth and polygonPoints to close them
                    groundTruth.pop();
                    polygonPoints.pop();
                }
            });
        }
    }
} else if (page === "results.php") {
    //the results page onle needs a simple button to copy the vcode to the clipboard
    const copyBtn = document.getElementById("copyVcodeBtn");
    const vcodeEl = document.getElementById("vcodeContainer");
    copyBtn.addEventListener("click", () => {
        navigator.clipboard.writeText(vcodeEl.innerText.trim());
        copyBtn.innerText = "Copied!";
    });
} else {
    //page === "about.php"
    //disable the link to about php - it is not needed
    document.querySelector('header a').remove();
}