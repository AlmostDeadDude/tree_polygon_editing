//check what is the page name, is it index.php or results.php
let page = window.location.pathname.split("/").pop();;

//in both cases:
//update the year in the footer
document.getElementById("year").innerHTML = new Date().getFullYear();

//specific for index.php and results.php
if (page === "index.php" || page === "") {
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

    //make tasks interactive
    let tasks = document.querySelectorAll(".task-wrapper");
    tasks.forEach(task => {
        //prepare the output object
        values[task.id] = {};
        //each task has a submit button with class nextBtn
        let nextBtn = task.querySelector(".nextBtn");
        nextBtn.disabled = true;

        //when the user clicks the button, the next task is shown
        nextBtn.addEventListener("click", () => {
            //save the results from the current task
            values[task.id] = polygonPoints;

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