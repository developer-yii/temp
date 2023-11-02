@component('mail::message')
# Account Approval Mail

<h3>Admin has approved your account, you can login now.</h3>
	@component('mail::button', ['url' => route('login')])
        Login
    @endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent