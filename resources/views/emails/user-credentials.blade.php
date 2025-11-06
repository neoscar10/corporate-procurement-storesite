@component('mail::message')
# Welcome, {{ $user->name }}

Your account has been created. Here are your credentials:

- **Email:** {{ $user->email }}
- **Password:** {{ $password }}

@isset($permissions)
    @if(count($permissions))
       - **Permissions granted:**
        @foreach($permissions as $p)
            - {{ $p }}
        @endforeach
    @endif
@endisset

@component('mail::button', ['url' => $loginUrl])
Accept
@endcomponent

If you didn’t expect this email, you can ignore it.

Thanks,<br>
{{ $appName }}
@endcomponent