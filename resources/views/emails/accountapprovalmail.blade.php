@component('mail::message')
# Account Approval Mail

<h3>A new user with the email {{ $user['email'] }} has been registered. Please approve this account for further use.</h3>
	@component('mail::button', ['url' => route('login')])
        Login
    @endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent