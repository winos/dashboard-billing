<?php
header("Content-Type:text/css");
$color = "#f0f"; // Change your Color Here
$secondColor = "#ff8"; // Change your Color Here

function checkhexcolor($color){
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
}

if (isset($_GET['color']) AND $_GET['color'] != '') {
    $color = "#" . $_GET['color'];
}

if (!$color OR !checkhexcolor($color)) {
    $color = "#336699";
}


function checkhexcolor2($secondColor){
    return preg_match('/^#[a-f0-9]{6}$/i', $secondColor);
}

if (isset($_GET['secondColor']) AND $_GET['secondColor'] != '') {
    $secondColor = "#" . $_GET['secondColor'];
}

if (!$secondColor OR !checkhexcolor2($secondColor)) {
    $secondColor = "#336699";
}

?>

.btn--base,.hero__subtitle::before,.hero__subtitle::after,.video-btn .icon::before, .video-btn .icon::after,.video-btn .icon,.video-btn .icon::before, .video-btn .icon::after,.about-thumb .about-img-content,.service-card::after,.section-subtitle.border-left::before,.testimonial-slide-area .content-slider .slick-arrow,.blog-card .post-time,.cta-wrapper,.footer-widget__title::before,.bg--base,.form-check-input:checked,.d-widget.curve--shape::before,.quick-link-card .icon::before,.header .main-menu li .sub-menu li a::before,.pagination .page-item.active .page-link,.pagination .page-item .page-link:hover,.sidebar-menu__header::before{
    background-color: <?php echo $color; ?> !important
}


.service-card__icon,.header .main-menu li a:hover, .header .main-menu li a:focus,.text--base,.footer-link-list li a:hover,.blog-title a:hover,.s-post__title a:hover,.page-breadcrumb li:first-child::before,.account-form .form-group a,.font-size--14px a,.user-account-check .form-check-input:checked ~ label i,.quick-link-card .icon,.quick-link-card:hover,.fw-bold,.inline-menu-list li a:hover,.header .main-menu li.menu_has_children:hover > a::before,.trans-serach-open-btn,.custom-select-search-box .search-box-btn,.sidebar-menu__item.active .sidebar-menu__link,.sidebar-menu__link:hover,.header-user-menu li a:hover{
    color: <?php echo $color; ?> !important
}

.header-user-menu li a:hover{
    background-color: <?php echo $color.'0d'; ?> !important
}

.pagination .page-item .page-link:hove,.sidebar-menu__item.active .sidebar-menu__link{
    border-color:<?php echo $color; ?> !important
}

.sidebar-menu__item.active .sidebar-menu__link{
    border-color:<?php echo $color; ?> !important
}

.d-widget{
    border-left: 4px solid <?php echo $color; ?> !important
}

.user-account-check .form-check-input:checked ~ label,.form-check-input:checked{
    border-color: <?php echo $color; ?> !important
}

.footer-contact-card .icon{
    background-color: <?php echo $color.'8c'; ?> !important
}
.footer-contact-card .icon::after{
    background-color: <?php echo $color.'40'; ?> !important
}

.service-card{
    border: 2px solid <?php echo $color.'73'; ?> !important
}

.service-card:hover{
    box-shadow: 0 5px 15px 1px <?php echo $color.'40'; ?> !important
}

.quick-link-card:hover .icon {
    color: #fff !important;
    border-color: <?php echo $color ?>;
}

.accordion-button:not(.collapsed){

    background-color:<?php echo $color.'1A'; ?> !important
}

.caption-list-two{

    background-color:<?php echo $color.'1A'; ?> !important
}

.accordion-button:focus{
    border-color: <?php echo $color.'1A'; ?> !important
}

.main-menu li.active > a{
    color: <?php echo $color ?>;
}

.preloader .animated-preloader, .preloader .animated-preloader::before{
    background: <?php echo $color ?>;
}

.subscription-form{
    border: 3px solid <?php echo $color ?>;
}