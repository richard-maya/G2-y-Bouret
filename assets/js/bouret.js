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


// COOKIES
// ------------------------------->
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function testCookie(){
    var estado = getCookie("cfg-cookie-check");
    if(!estado){
        $('#cookie-alert').removeClass('d-none');
        setTimeout(function () {
            setCookie('cfg-cookie-check', true, 365);
        }, 3000);
    }
    // document.cookie = "cfg-cookie-check=false; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
}


// FORM VALIDATION
// ------------------------------->
$(document).on('blur', '[data-validator]', function () {
    new Validator($(this), {
        language: {
            required: 'Éste campo es requerido.',
            email: 'Ingresa un correo válido.',
        }
    });

    if ($('#nombre').hasClass("is-valid") && $('#correo').hasClass("is-valid") && $('#mensaje').hasClass("is-valid")) {
        $('#submit').removeAttr("disabled", "disabled");
    }
});


// DOCUMENT READY
// ------------------------------->
$(document).ready(function () {
    "use strict";
    // testCookie();
    wow.init();

    $("p.success-message").addClass("wow zoomIn");
    $("h6").addClass("wow fadeInUp");
    $("h5").addClass("wow fadeInUp");
    $("h4").addClass("wow fadeInUp");
    $("h3").addClass("wow fadeInUp");
    $("h2").addClass("wow fadeInUp");
    $("h1").addClass("wow fadeInUp");
    $("p").addClass("wow fadeInUp");
    $("label").addClass("wow fadeInUp");
    $("img:not(#navbar-brand-logo)").addClass("wow fadeInUp");

    var link = document.location.search;

    if (link.includes("success")) {
        $('#message-modal').modal('show');
    }
    if ($('.home-section').length) {
        $('.home-section').parallax({ imageSrc: 'assets/img/g2-y-bouret.png' });
    }
    if ($('.services-section').length) {
        $('.services-section').parallax({ imageSrc: 'assets/img/consultoria-gubernamental.png' });
    }
});