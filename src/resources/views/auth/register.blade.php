@extends('layouts.auth')

@section('title', '会員登録')

@section('content')
<div class="auth-form">
    <h2 class="auth-form__title">会員登録</h2>
    <form action="{{ route('register') }}" method="POST" novalidate>
        @csrf
        <div class="auth-form__group">
            <label class="auth-form__label" for="name">名前</label>
            <input class="auth-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
            @error('name')
                <p class="auth-form__error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-form__group">
            <label class="auth-form__label" for="email">メールアドレス</label>
            <input class="auth-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <p class="auth-form__error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-form__group">
            <label class="auth-form__label" for="password">パスワード</label>
            <input class="auth-form__input" type="password" name="password" id="password">
            @error('password')
                <p class="auth-form__error" role="alert">{{ $message }}</p>
            @enderror
        </div>
        <div class="auth-form__group">
            <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
            <input class="auth-form__input" type="password" name="password_confirmation" id="password_confirmation">
        </div>
        <button class="auth-form__button" type="submit">登録する</button>
    </form>
    <a class="auth-form__link" href="{{ route('login') }}">ログインはこちら</a>
</div>
@endsection
