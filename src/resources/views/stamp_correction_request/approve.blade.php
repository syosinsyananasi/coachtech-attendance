@extends('layouts.admin')

@section('title', '修正申請詳細')

@php
    $isApproved = $isApproved ?? false;
@endphp

@section('content')
<div class="content-page">
    <div class="content-page__inner">
        <h2 class="content-page__title">勤怠詳細</h2>

        <div class="detail-card detail-card--bordered">
            <div class="detail-card__row">
                <span class="detail-card__label">名前</span>
                <span class="detail-card__value detail-card__value--date">{{ $attendance->user->name ?? '' }}</span>
            </div>

            <div class="detail-card__row">
                <span class="detail-card__label">日付</span>
                <div class="detail-card__value">
                    <span class="detail-card__text">{{ $year ?? '2023' }}年</span>
                    <div class="detail-card__separator"></div>
                    <span class="detail-card__text">{{ $monthDay ?? '6月1日' }}</span>
                </div>
            </div>

            <div class="detail-card__row">
                <span class="detail-card__label">出勤・退勤</span>
                <div class="detail-card__value">
                    <div class="detail-card__text">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</div>
                    <span class="detail-card__separator">〜</span>
                    <div class="detail-card__text">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</div>
                </div>
            </div>

            @forelse ($rests ?? [] as $index => $rest)
            <div class="detail-card__row">
                <span class="detail-card__label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                <div class="detail-card__value">
                    <div class="detail-card__text">{{ $rest['start'] ?? '' }}</div>
                    <span class="detail-card__separator">〜</span>
                    <div class="detail-card__text">{{ $rest['end'] ?? '' }}</div>
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
                    <div>{{ $attendance->note ?? '' }}</div>
                </div>
            </div>
        </div>

        <div class="detail__actions">
            @if ($isApproved)
                <span class="detail__button detail__button--disabled">承認済み</span>
            @else
                <form action="{{ route('correction_request.storeApproval', $correctionRequest->id ?? '') }}" method="POST" novalidate>
                    @csrf
                    <button class="detail__button" type="submit">承認</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
