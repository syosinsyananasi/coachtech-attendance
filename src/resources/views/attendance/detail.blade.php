@extends('layouts.app')

@section('title', '勤怠詳細')

@php
    $isPending = $isPending ?? false;
@endphp

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">勤怠詳細</h2>

        <form action="/attendance/detail/{{ $attendance->id ?? '' }}" method="POST" novalidate>
            @csrf
            <div class="detail-card {{ $isPending ? 'detail-card--bordered' : '' }}">
                <div class="detail-card__row">
                    <span class="detail-card__label">名前</span>
                    <span class="detail-card__value">{{ $attendance->user->name ?? '' }}</span>
                </div>

                <div class="detail-card__row">
                    <span class="detail-card__label">日付</span>
                    <div class="detail-card__value">
                        <span>{{ $year ?? '2023' }}年</span>
                        <span>{{ $monthDay ?? '6月1日' }}</span>
                    </div>
                </div>

                <div class="detail-card__row">
                    <span class="detail-card__label">出勤・退勤</span>
                    <div class="detail-card__value">
                        @if ($isPending)
                            <span>{{ $attendance->clock_in ?? '' }}</span>
                        @else
                            <input class="detail-card__input" type="text" name="clock_in" value="{{ $attendance->clock_in ?? '' }}">
                        @endif
                        <span>〜</span>
                        @if ($isPending)
                            <span>{{ $attendance->clock_out ?? '' }}</span>
                        @else
                            <input class="detail-card__input" type="text" name="clock_out" value="{{ $attendance->clock_out ?? '' }}">
                        @endif
                    </div>
                </div>

                @forelse ($rests ?? [] as $index => $rest)
                <div class="detail-card__row">
                    <span class="detail-card__label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                    <div class="detail-card__value">
                        @if ($isPending)
                            <span>{{ $rest['start'] ?? '' }}</span>
                        @else
                            <input class="detail-card__input" type="text" name="rests[{{ $index }}][start]" value="{{ $rest['start'] ?? '' }}">
                        @endif
                        <span>〜</span>
                        @if ($isPending)
                            <span>{{ $rest['end'] ?? '' }}</span>
                        @else
                            <input class="detail-card__input" type="text" name="rests[{{ $index }}][end]" value="{{ $rest['end'] ?? '' }}">
                        @endif
                    </div>
                </div>
                @empty
                @if (!$isPending)
                <div class="detail-card__row">
                    <span class="detail-card__label">休憩</span>
                    <div class="detail-card__value">
                        <input class="detail-card__input" type="text" name="rests[0][start]" value="">
                        <span>〜</span>
                        <input class="detail-card__input" type="text" name="rests[0][end]" value="">
                    </div>
                </div>
                @endif
                @endforelse

                <div class="detail-card__row">
                    <span class="detail-card__label">備考</span>
                    <div class="detail-card__value">
                        @if ($isPending)
                            <span>{{ $attendance->note ?? '' }}</span>
                        @else
                            <textarea class="detail-card__textarea" name="note">{{ $attendance->note ?? '' }}</textarea>
                        @endif
                    </div>
                </div>
            </div>

            @if ($isPending)
                <p class="detail__warning">*承認待ちのため修正はできません。</p>
            @else
                <div class="detail__actions">
                    <button class="detail__button" type="submit">修正</button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
