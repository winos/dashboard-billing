@php
    $addTokenUrl = route(strToLower(userGuard()['type'] ?? 'user').'.add.device.token');
@endphp

<script src="{{asset('assets/global/js/firebase/firebase-8.3.2.js')}}"></script>

<script>
    "use strict";

    var permission = null;
    
    @if (request()->is('agent/*'))
        var authenticated = '{{ auth('agent')->user() ? true : false }}';
    @elseif (request()->is('merchant/*'))
        var authenticated = '{{ auth('merchant')->user() ? true : false }}';
    @else
        var authenticated = '{{ auth()->user() ? true : false }}';
    @endif
    
    var pushNotify = @json(gs('pn'));
    var firebaseConfig = @json(gs('firebase_config'));

    function pushNotifyAction(){
        permission = Notification.permission;

        if(!('Notification' in window)){
            notify('info', 'Push notifications not available in your browser. Try Chromium.')
        }
        else if(permission === 'denied' || permission == 'default'){ 
            $('.notice').append(`
                <div class='card mb-4'>
                    <div class="d-user-notification d-flex flex-wrap align-items-center">
                        <div class="icon text--warning">
                            <i class="las la-bell-slash"></i>
                        </div>
                        <div class="content">
                            <p class="text-white fw--bold">@lang('Please Allow / Reset Browser Notification').</p>
                        </div>
                    </div>
                    <div class='card-body text-center'>
                        @lang('If you want to get push notification then you have to allow notification from your browser')
                    </div>
                </div>
            `);
        }
    }

    //If enable push notification from admin panel
    if(pushNotify == 1){
        pushNotifyAction();
    }

    //When users allow browser notification
    if(permission != 'denied' && firebaseConfig){

        //Firebase
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        navigator.serviceWorker.register("{{ asset('assets/global/js/firebase/firebase-messaging-sw.js') }}")

        .then((registration) => {
            messaging.useServiceWorker(registration);

            function initFirebaseMessagingRegistration() {
                messaging
                .requestPermission()
                .then(function () {
                    return messaging.getToken()
                })
                .then(function (token){
                    $.ajax({
                        url: '{{ $addTokenUrl }}',
                        type: 'POST',
                        data: {
                            token: token,
                            '_token': "{{ csrf_token() }}"
                        },
                        success: function(response){
                        },
                        error: function (err) {
                        },
                    });
                }).catch(function (error){
                });
            }

            messaging.onMessage(function (payload){
                const title = payload.notification.title;
                const options = {
                    body: payload.notification.body,
                    icon: payload.data.icon,
                    image: payload.notification.image,
                    click_action:payload.data.click_action,
                    vibrate: [200, 100, 200]
                };
                new Notification(title, options);
            });

            //For authenticated users
            if(authenticated){
                initFirebaseMessagingRegistration();
            }

        });

    }
</script>
