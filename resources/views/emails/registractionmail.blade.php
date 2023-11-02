@component('mail::message')
# Registration Mail

<h3>Your account has been successfully registered. The admin will review and approve your account, after which you will be able to log in.</h3>
<b>Email : </b>{{ $validatedData['email'] }}<br>
	@component('mail::button', ['url' => route('login')])
        Login
    @endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent