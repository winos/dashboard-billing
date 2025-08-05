'use strict';

// menu options custom affix
var fixed_top = $(".header");
$(window).on("scroll", function () {
  if ($(window).scrollTop() > 50) {
    fixed_top.addClass("animated fadeInDown menu-fixed");
  } else {
    fixed_top.removeClass("animated fadeInDown menu-fixed");
  }
});

// mobile menu js
$(".navbar-collapse>ul>li>a, .navbar-collapse ul.sub-menu>li>a").on("click", function () {
  const element = $(this).parent("li");
  if (element.hasClass("open")) {
    element.removeClass("open");
    element.find("li").removeClass("open");
  } else {
    element.addClass("open");
    element.siblings("li").removeClass("open");
    element.siblings("li").find("li").removeClass("open");
  }
});



// wow js init
new WOW().init();

// lightcase plugin init
$('a[data-rel^=lightcase]').lightcase();

// main wrapper calculator
var bodySelector = document.querySelector('body');
var header = document.querySelector('.header');
var footer = document.querySelector('.footer-section');
(function () {
  if (bodySelector.contains(header) && bodySelector.contains(footer)) {
    var headerHeight = document.querySelector('.header').clientHeight;
    var footerHeight = document.querySelector('.footer-section').clientHeight;

    // if header isn't fixed to top
    var totalHeight = parseInt(headerHeight, 10) + parseInt(footerHeight, 10) + 'px';

    // if header is fixed to top
    // var totalHeight = parseInt( footerHeight, 10 ) + 'px'; 

    var minHeight = '100vh';
    document.querySelector('.main-wrapper').style.minHeight = `calc(${minHeight} - ${totalHeight})`;
  }
})();

// Animate the scroll to top
$(".scroll-top").on("click", function (event) {
  event.preventDefault();
  $("html, body").animate({
    scrollTop: 0
  }, 300);
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip({
    boundary: 'window'
  })
});

// custom input animation js
$('.custom--form-group .form--control').on('input', function () {
  let passfield = $(this).val();
  if (passfield.length < 1) {
    $(this).removeClass('hascontent');
  } else {
    $(this).addClass('hascontent');
  }
});

//preloader js code
$(".preloader").delay(300).animate({
	"opacity" : "0"
	}, 300, function() {
	$(".preloader").css("display","none");
});

/* ==============================
					slider area
================================= */

// brand-slider
$('.brand-slider').slick({
  // autoplay: true,
  autoplaySpeed: 2000,
  dots: false,
  infinite: true,
  speed: 300,
  slidesToShow: 5,
  arrows: false,
  slidesToScroll: 1,
  responsive: [{
      breakpoint: 600,
      settings: {
        slidesToShow: 3,
      }
    },
    {
      breakpoint: 450,
      settings: {
        slidesToShow: 2,
      }
    }
  ]
});

// payment-method-slider
$('.testimonial-slider').slick({
  // autoplay: true,
  autoplaySpeed: 2000,
  dots: false,
  infinite: true,
  speed: 300,
  slidesToShow: 1,
  arrows: true,

  slidesToScroll: 1
});


$('.testimonial-slide-area .thumb-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  asNavFor: '.testimonial-slide-area .content-slider'
});
$('.testimonial-slide-area .content-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  asNavFor: '.testimonial-slide-area .thumb-slider',
  dots: false,
  arrows: true,
  prevArrow: '<div class="prev"><i class="las la-angle-left"></i></div>',
  nextArrow: '<div class="next"><i class="las la-angle-right"></i></div>',
});


const searchOpenBtn = $('.trans-serach-open-btn');
const transForm = $('.transaction-top-form');

searchOpenBtn.on('click', function () {
  transForm.slideToggle();
});

Array.from(document.querySelectorAll('table')).forEach(table => {
  let heading = table.querySelectorAll('thead tr th');
  Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
     
      if (colum.hasAttribute('colspan') && i == 0) {
        return false;
      }

      colum.setAttribute('data-label', heading[i].innerText)
    });
  });
});
