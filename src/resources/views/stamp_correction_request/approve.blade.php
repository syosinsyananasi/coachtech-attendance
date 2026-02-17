@extends('layouts.admin')

@section('title', '修正申請詳細')

@php
    $isApproved = $isApproved ?? false;
@endphp

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">勤怠詳細</h2>

        <div class="detail-card">
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
                    <span>{{ $attendance->clock_in ?? '' }}</span>
                    <span>〜</span>
                    <span>{{ $attendance->clock_out ?? '' }}</span>
                </div>
            </div>

            @forelse ($rests ?? [] as $index => $rest)
            <div class="detail-card__row">
                <span class="detail-card__label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                <div class="detail-card__value">
                    <span>{{ $rest['start'] ?? '' }}</span>
                    <span>〜</span>
                    <span>{{ $rest['end'] ?? '' }}</span>
                </div>
            </div>
            @empty
            <div class="detail-card__row">
                <span class="detail-card__label">休憩</span>
                <div class="detail-card__value"></div>
            </div>
            @endforelse

            <div class="detail-card__row">
                <span class="detail-card__label">備考</span>
                <div class="detail-card__value">
                    <span class="detail-card__note">{{ $attendance->note ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="detail__actions">
            @if ($isApproved)
                <span class="detail__button detail__button--disabled">承認済み</span>
            @else
                <form action="/stamp_correction_request/approve/{{ $correctionRequest->id ?? '' }}" method="POST" novalidate>
                    @csrf
                    <button class="detail__button" type="submit">承認</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
