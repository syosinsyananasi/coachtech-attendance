@extends('layouts.auth')

@section('title', 'メール認証')

@section('content')
<div class="verify">
    <p class="verify__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    <a class="verify__button" href="{{ url('/email/verify') }}">認証はこちらから</a>
    <form class="verify__form" action="/email/verification-notification" method="POST">
        @csrf
        <button class="verify__link" type="submit">認証メールを再送する</button>
    </form>
</div>
@endsection
