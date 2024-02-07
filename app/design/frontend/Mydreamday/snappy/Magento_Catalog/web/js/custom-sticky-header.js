window.addEventListener('scroll', function(){
    var header = document.querySelector('.header-content-s');
    header.classList.toggle("sticky", window.scrollY > 0);
});

document.addEventListener("DOMContentLoaded", function() {

    function processNewMessage(el) {
        let btnClose = document.createElement('button');
        btnClose.type = 'button';
        btnClose.classList.add('button-close');
        btnClose.textContent = '';  // Or whatever text or icon you want for the close button
        el.appendChild(btnClose);

        // Add the 'shown' class shortly after
        setTimeout(function() {
            el.classList.add('shown');
        }, 0);

        let timeoutId;
        let initialTimeout = 10000;  // 10 seconds
        let hoverStartTime;

        // Schedule removal of the 'shown' class
        function scheduleRemoval(duration) {
            timeoutId = setTimeout(function() {
                el.classList.remove('shown');
            }, duration);
        }

        scheduleRemoval(initialTimeout);

        // If hovered, clear the timeout to prevent removal and record hover start time
        el.addEventListener('mouseover', function() {
            clearTimeout(timeoutId);
            hoverStartTime = Date.now();
        });

        // If no longer hovered, reschedule the removal with the remaining time
        el.addEventListener('mouseout', function() {
            let hoverDuration = Date.now() - hoverStartTime;
            let remainingTimeout = initialTimeout - hoverDuration;
            scheduleRemoval(remainingTimeout);
        });
    }

    // Process existing '.message' elements on page load
    let messages = document.querySelectorAll('.message');
    messages.forEach(processNewMessage);

    // Listen for click events on '.message > .button-close'
    document.addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('button-close')) {
            let message = event.target.closest('.message');
            message.classList.remove('shown');

            // Remove the message from the DOM shortly after
            setTimeout(function() {
                message.parentNode.removeChild(message);
            }, 100);
        }
    });

    // Observe the parent container for changes and detect new '.message' elements
    const observer = new MutationObserver(function(mutationsList) {
        for(let mutation of mutationsList) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE && node.matches('.message')) {
                        processNewMessage(node);
                    }
                });
            }
        }
    });

    // Start observing the document with the configured parameters
    observer.observe(document.body, { childList: true, subtree: true });
});
