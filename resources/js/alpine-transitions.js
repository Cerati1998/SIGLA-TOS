/**
 * Alpine rejects a hide promise when an element is shown again while it is hiding, and it can happen before
 * its own catch is attached. Alpine ignores this rejection, so it should not reach the console as an error.
 */
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason?.isFromCancelledTransition) {
        event.preventDefault();
    }
});
