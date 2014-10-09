/**
 * This enables user to scroll to certain anchor in a page
 */

// This should work on elements

function scrollTo(selector){
    $('html, body').animate({
            scrollTop: $(selector).offset().top-50
    }, 'slow');
}