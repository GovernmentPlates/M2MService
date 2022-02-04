/*
* Clock.js
* Displays the clock on the navigation bar
* Uses jQuery and JavaScript to get time
* Author: D Hollis <p2533140@my365.dmu.ac.uk> (Team 21-3110-AS)
 */
var clockEl = document.querySelector('#clock');

function getTime() {
  return new Date().toLocaleTimeString('en-GB', 
     { hour12: false, hour: 'numeric', minute: 'numeric' }).toString();
}

function setTime() {
  var time = getTime();
  // check if the colon is there
  if (clockEl.innerText.split(':').length === 1) {
    // if it's not, set the actual time
    clockEl.innerText = time;
  } else {
    // if it is, remove the colon
    clockEl.innerText = time.split(':').join(' ');
  }
}

setInterval( setTime , 1000);
setTime();