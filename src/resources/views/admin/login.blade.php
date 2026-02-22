@extends('layouts.auth')

@section('title', '管理者ログイン')

@section('content')
<div class="auth-form">
    <h2 class="auth-form__title">管理者ログイン</h2>

    <form action="{{ route('admin.login.store') }}" method="POST" novalidate>
        @csrf
        <div class="auth-form__group">
            <label class="auth-form__label" for="email">メールアドレス</label>
            <input class="auth-form__input auth-form__input--large" type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="auth-form__error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form__group">
            <label class="auth-form__label" for="password">パスワード</label>
            <input class="auth-form__input auth-form__input--large" type="password" id="password" name="password">
            @error('password')
                <p class="auth-form__error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button class="auth-form__button" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection
