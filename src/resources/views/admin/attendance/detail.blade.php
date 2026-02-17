@extends('layouts.admin')

@section('title', '勤怠詳細（管理者）')

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">勤怠詳細</h2>

        <form action="/admin/attendance/{{ $attendance->id ?? '' }}" method="POST" novalidate>
            @csrf
            <div class="detail-card detail-card--bordered">
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
                        <input class="detail-card__input" type="text" name="clock_in" value="{{ $attendance->clock_in ?? '' }}">
                        <span>〜</span>
                        <input class="detail-card__input" type="text" name="clock_out" value="{{ $attendance->clock_out ?? '' }}">
                    </div>
                </div>

                @forelse ($rests ?? [] as $index => $rest)
                <div class="detail-card__row">
                    <span class="detail-card__label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                    <div class="detail-card__value">
                        <input class="detail-card__input" type="text" name="rests[{{ $index }}][start]" value="{{ $rest['start'] ?? '' }}">
                        <span>〜</span>
                        <input class="detail-card__input" type="text" name="rests[{{ $index }}][end]" value="{{ $rest['end'] ?? '' }}">
                    </div>
                </div>
                @empty
                <div class="detail-card__row">
                    <span class="detail-card__label">休憩</span>
                    <div class="detail-card__value">
                        <input class="detail-card__input" type="text" name="rests[0][start]" value="">
                        <span>〜</span>
                        <input class="detail-card__input" type="text" name="rests[0][end]" value="">
                    </div>
                </div>
                @endforelse

                <div class="detail-card__row">
                    <span class="detail-card__label">備考</span>
                    <div class="detail-card__value">
                        <textarea class="detail-card__textarea" name="note">{{ $attendance->note ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="detail__actions">
                <button class="detail__button" type="submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection
