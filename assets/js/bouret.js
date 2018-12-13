// HEADER
// ------------------------------->
$(document).on("scroll", function () {
    "use strict";
    if ($(document).scrollTop() > 50) {
        $(".navbar").addClass("navbar-small");
    } else {
        $(".navbar").removeClass("navbar-small");
    }
});


// SMOOTH SCROLL
// ------------------------------->
var scroll = new SmoothScroll('a[href*="#"]', {
    speed: 1000,
    easing: 'easeInOutQuint'
});


// WOW JS
// ------------------------------->
var wow = new WOW({
    offset: -20,
    mobile: false
});


// DOCUMENT READY
// ------------------------------->
$(document).ready(function () {
    "use strict";
    wow.init();
    // $("p:not(.success-message)").addClass("wow fadeInUp");
    // $("p.success-message").addClass("wow zoomIn");
    $("h6").addClass("wow fadeInUp");
    $("h5").addClass("wow fadeInUp");
    $("h4").addClass("wow fadeInUp");
    $("h3").addClass("wow fadeInUp");
    $("h2").addClass("wow fadeInUp");
    $("h1").addClass("wow fadeInUp");
    $("p").addClass("wow fadeInUp");
    $("label").addClass("wow fadeInUp");
    $("img:not(#navbar-brand-logo)").addClass("wow fadeInUp");
    // $("li:not(.nav-item)").addClass("wow fadeInUp");
    // $("i.fa-facebook-square").addClass("wow fadeInUp");
    // $("i.fa-twitter").addClass("wow fadeInUp");

    var link = document.location.search;

    if (link.includes("success")) {
        $('#message-modal').modal('show');
    }

    $('.home-section').parallax({ imageSrc: 'assets/img/g2-y-bouret.png' });
    $('.services-section').parallax({ imageSrc: 'assets/img/consultoria-gubernamental.png' });
});