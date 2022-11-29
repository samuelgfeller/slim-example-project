/**
 * This function counts down seconds spans with class name
 * "throttle-time-span" loaded in dom.
 *
 */
export function countDownThrottleTimer(){
    let timeSpans = document.getElementsByClassName('throttle-time-span');
        for (const timeSpan of timeSpans) {
            if (timeSpan !== null) {
                let formSubmitBtn = timeSpan.closest('form').querySelector('input[type="submit"]');
                formSubmitBtn.disabled = true;
                let timeInSec = parseInt(timeSpan.innerHTML);
                let timer = setInterval(function () {
                    timeSpan.textContent = timeInSec;
                    if (--timeInSec < 0) {
                        formSubmitBtn.disabled = false;
                        timeInSec = 0;
                        timeSpan.parentElement.style.display = 'none';
                        clearInterval(timer);
                    }
                }, 1000);
            }
        }
}