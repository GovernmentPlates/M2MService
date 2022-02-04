/*
* loader.js script
* Does absolutely nothing, apart from show a pretty loader on the screen (useful if waiting for large content to render)
* Author: D Hollis <p2533140@my365.dmu.ac.uk> (Team 21-3110-AS)
 */
document.onreadystatechange = function() {
    if (document.readyState !== "complete") {
        document.querySelector(
            "body").style.visibility = "hidden";
        document.querySelector(
            "#loader").style.visibility = "visible";
    } else {
        document.querySelector(
            "#loader").style.display = "none";
        document.querySelector(
            "body").style.visibility = "visible";
    }
};