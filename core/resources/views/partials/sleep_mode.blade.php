
@if(gs('detect_activity'))

@push('script')
<script>
    "use strict";   

    (function ($) {
      
        var idleState = false;
		var idleTimer = null;
		var sessionStatus = null;

        var html = `
            <div class="sleep-wrapper">
                <div class="sleep-wrapper__thumb"> 
                    <img class="" src="{{ getSleepImage() }}" alt="@lang('Sleep')">
                </div>
                <form class='form'>
                    <div class="avatar">
                        <div class="avatar__icon"><i class="fas fa-user-alt"></i></div>
                        <div class="avatar__content">
                            <h5 class="avatar__title">{{ @userGuard()['user']->fullname }}</h5>
                            <button class="sleep__button" type="submit">Continue</button>
                        </div>
                    </div>
                </form>
                
                <div class="sleep-bottom">
                    <div class="sleep-bottom__time">
                        <div id="clockDisplay" class="clock" onload="showTime()"></div>
                    </div>
                    <div class="sleep-bottom__menu btn-group dropup">
                        <button type="button" class="sleep-bottom-menu__button dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-home"></i>
                        </button>
                        <div class="sleep-bottom-dropdown dropdown-menu" x-placement="top-start"">
                            <a class="dropdown-item" href="{{ route('home') }}">
                                <span class="dropdown-item__icon"><i class="fas fa-home"></i></span> @lang('Home')
                            </a>
                            <a class="dropdown-item logoutBtn" href="{{ route('user.logout') }}">
                                <span class="dropdown-item__icon"><i class="fas fa-sign-out-alt"></i></span> @lang('Log Out')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        function sleep(submit = false){
            clearTimeout(idleTimer);
 
            if (idleState == true && submit) { 
                $('body >.sleep-wrapper').fadeOut(700, function(){ 
                    $.ajax({
                        url: "{{ route('session.status') }}",
                        method: "get",
                        data: {
                            'reload': true
                        }
                    }); 
                    checkSession(300000); // 300000 = 5 minute
                    return $("body").removeClass('overflow-hidden');         
                });
            }

            idleState = false;
     
            idleTimer = setTimeout(function () {  
                $("body").addClass('overflow-hidden');
                $('body >.sleep-wrapper').fadeIn(600);
                idleState = true; 
                checkSession(180000);  // 180000 = 3 minute
            }, 180000);  // 180000 = 3 minute

        }

        function checkSession(duration){
            clearTimeout(sessionStatus);

            sessionStatus = setTimeout(function () { 

                $.ajax({
                    url: "{{ route('session.status') }}",
                    method: "get",
                    data: {
                        'userType': '{{ @$userType }}'
                    },
                    success: function (success) {
                        if(success.status == 404){
                            window.location.reload();
                        }
                    }
                }); 
                
            }, duration);
        }

        $('body').append(html);
        $('body >.sleep-wrapper').hide();

        $('*').bind(
            "blur focus focusin focusout load resize scroll unload click" + 
            " dblclick mousedown mouseup mousemove mouseover mouseout mouseenter " + 
            "mouseleave change select submit keydown keypress keyup error", function (e) {
            return sleep();
        });

        $('.form').on('submit', function(e){
            e.preventDefault();
            idleState = true;
            return sleep(true);
        });


        // ====================================================================================
        // ==================================  Clock ==========================================
        // ====================================================================================

        function showTime(){
            var date = new Date();
            var h = date.getHours(); // 0 - 23
            var m = date.getMinutes(); // 0 - 59
            var s = date.getSeconds(); // 0 - 59
            var session = "AM";
            
            if(h == 0){
                h = 12;
            }
            
            if(h > 12){
                h = h - 12;
                session = "PM";
            }
            
            h = (h < 10) ? "0" + h : h;
            m = (m < 10) ? "0" + m : m;
            s = (s < 10) ? "0" + s : s;
            
            var time = h + ":" + m + ":" + s + " " + session;
            document.getElementById("clockDisplay").innerText = time;
            document.getElementById("clockDisplay").textContent = time;
            
            setTimeout(showTime, 1000);
        }
        showTime();

        // ====================================================================================
        // ==================================  Clock ==========================================
        // ====================================================================================

    })(jQuery);

</script>
@endpush

@push('style')
<style>
    .sleep-wrapper {
        z-index: 999;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 99;
    }
    .sleep-wrapper .sleep-wrapper {
        /* display: block !important; */
    }
    .sleep-wrapper::-webkit-scrollbar {
        width: 0; 
        background: transparent;  
    }
    .sleep-wrapper__thumb {
        position: absolute;
        left: -5%;
        top: -5%;
        filter: blur(15px);
        width: 110%;
        height: 110%;
        z-index: -1;
        right: -5%;
        bottom: -5%;
    }
    .sleep-wrapper__thumb::before {
        position: absolute;
        content: "";
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        background-color: #000000;
        z-index: -1;
    }
    .sleep-wrapper__thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar {
        display: flex;
        flex-direction: column;
        position: absolute;
        width: 100%;
        height: 100%;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    .avatar__icon {
        width: 200px;
        height: 200px;
        background-color: #ffffff24;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 100px;
        border-radius: 50%;
        color: #fff;
    }
    @media (max-width: 767px) {
        .avatar__icon {
            width: 150px;
            height: 150px;
            font-size: 70px;
        }
    }
    @media (max-width: 575px) {
        .avatar__icon {
            width: 120px;
            height: 120px;
            font-size: 50px;
        }
    }
    .avatar__title {
        margin-top: 20px;
        color: #fff;
        margin-bottom: 10px;
    }
    .sleep__button {
        background-color: #ffffff36;
        padding: 4px 30px;
        color: #fff;
        font-size: 15px;
        border-radius: 5px;
        border: 2px solid transparent;
        transition: .1s linear; 
        font-size: 15px; 
    }
    .sleep__button:hover, .sleep__button:focus {
        border: 2px solid #ffffff30 !important;
    }
    .avatar__content {
        text-align: center;
    }

    /* ==================================================================================== */
    /* ==================================  Clock Start ==================================== */
    /* ==================================================================================== */
    .clock {
        color: #fff;
        font-size: 16px;
        letter-spacing: 2px;
        line-height: 1
    }
    /* ==================================================================================== */
    /* ==================================  Clock End ====================================== */
    /* ==================================================================================== */


    /* ==================================================================================== */
    /* ==================================  Dropdown Start ================================= */
    /* ==================================================================================== */
    .sleep-bottom__menu {
        margin-left: 15px;
    }
    .sleep-bottom {
        position: absolute;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;    
    }
    .sleep-bottom-menu__button {
        background-color: #ffffff29;
        border: 0;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 5px;
        color: #ffffffc7;
    }
    .sleep-bottom-menu__button.dropdown-toggle::after {
        display: none;
    }
    .sleep-bottom-dropdown {
        background-color: #0e0e0ea8;
        padding: 10px;
        border-radius: 5px;
        border: 0;
    }
    .sleep-bottom-dropdown .dropdown-item {
        color: #ffffffc7;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 15px;
    }
    .sleep-bottom-dropdown .dropdown-item__icon {
        font-size: 13px; 
        margin-right: 3px;
    }
    .sleep-bottom-dropdown .dropdown-item:hover, .sleep-bottom-dropdown .dropdown-item:focus {
        background-color: #ffffff1a;
    }
    /* ==================================================================================== */
    /* ==================================  Dropdown End =================================== */
    /* ==================================================================================== */

</style>
@endpush

@endif

