// ==========================================
// CONFIGURATION (Edit these to swap things out)
// ==========================================
var CONFIG = {
    secretCode: "showcolors",
    youtubeUrl: "https://www.youtube.com/embed/RMM09W2tec8?autoplay=1",
    secretBg: "url('../images/colors.png')",
    defaultBg: "url('../images/backgrey.png')"
};

// ==========================================
// CORE LOGIC (Do not edit below this line)
// ==========================================
var keyBuffer = "";

document.addEventListener("keydown", function(event) {
    // EXCEPTION HANDLER: The "try" block attempts to run the code.
    try {
        // 1. Safe Input Check: We first make sure event.target and tagName actually exist!
        // If they do, and it's an input box, we stop the script.
        if (event.target && event.target.tagName && event.target.tagName.toLowerCase() === 'input') {
            return;
        }

        // 2. Safe Key Check: If the key doesn't exist (like a weird system volume button), stop.
        if (!event.key) return;

        // Now we know for a fact it's safe to use toLowerCase()
        var key = event.key.toLowerCase();
        keyBuffer += key;

        // Keep the buffer trimmed (I removed your duplicated block here!)
        if (keyBuffer.length > CONFIG.secretCode.length) {
            keyBuffer = keyBuffer.substring(keyBuffer.length - CONFIG.secretCode.length);
        }

        // Trigger the secret
        if (keyBuffer === CONFIG.secretCode) {
            triggerSecret();
        }
    } 
    // EXCEPTION HANDLER: The "catch" block catches any crashes secretly.
    catch (error) {
        // This will print the error silently in your F12 console instead of breaking the page.
        console.error("Easter Egg encountered a minor hiccup, safely caught: ", error);
    }
});

function triggerSecret() {
    document.getElementById('pageBody').style.backgroundImage = CONFIG.secretBg;
    document.getElementById('secretVideo').src = CONFIG.youtubeUrl;
    document.getElementById('videoContainer').style.display = "block";
    document.getElementById('resetButton').style.display = "block";
    document.getElementById('title').innerText = "CIS 4004 Contact Manager";
    keyBuffer = ""; 
}

function turnOffSecret() {
//  should we keep the colorful background image or reset it?
//  If you comment out the two lines below, the background resets to grey and title reverts to COP
//  document.getElementById('pageBody').style.backgroundImage = CONFIG.defaultBg;
//  document.getElementById('title').innerText = "COP 4331 LAMP STACK DEMO"
    document.getElementById('secretVideo').src = "";
    document.getElementById('videoContainer').style.display = "none";
    document.getElementById('resetButton').style.display = "none";
;
}

