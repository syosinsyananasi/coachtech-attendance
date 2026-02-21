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
                        @if ($isPending)
                            <div class="detail-card__text">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</div>
                        @else
                            <input class="detail-card__input" type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}">
                        @endif
                        <span class="detail-card__separator">〜</span>
                        @if ($isPending)
                            <div class="detail-card__text">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</div>
                        @else
                            <input class="detail-card__input" type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}">
                        @endif
                    </div>
                </div>
                @if ($errors->has('clock_in') || $errors->has('clock_out'))
                <div class="detail-card__row">
                    <span class="detail-card__label"></span>
                    <div class="detail-card__value">
                        @error('clock_in') <span class="detail-card__error">{{ $message }}</span> @enderror
                        @error('clock_out') <span class="detail-card__error">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                @foreach ($rests ?? [] as $index => $rest)
                <div class="detail-card__row">
                    <span class="detail-card__label">休憩{{ $index > 0 ? $index + 1 : '' }}</span>
                    <div class="detail-card__value">
                        @if ($isPending)
                            <div class="detail-card__text">{{ $rest['start'] ?? '' }}</div>
                        @else
                            <input class="detail-card__input" type="text" name="rests[{{ $index }}][start]" value="{{ old("rests.{$index}.start", $rest['start'] ?? '') }}">
                        @endif
                        <span class="detail-card__separator">〜</span>
                        @if ($isPending)
                            <div class="detail-card__text">{{ $rest['end'] ?? '' }}</div>
                        @else
                            <input class="detail-card__input" type="text" name="rests[{{ $index }}][end]" value="{{ old("rests.{$index}.end", $rest['end'] ?? '') }}">
                        @endif
                    </div>
                </div>
                @if ($errors->has("rests.{$index}.start") || $errors->has("rests.{$index}.end"))
                <div class="detail-card__row">
                    <span class="detail-card__label"></span>
                    <div class="detail-card__value">
                        @error("rests.{$index}.start") <span class="detail-card__error">{{ $message }}</span> @enderror
                        @error("rests.{$index}.end") <span class="detail-card__error">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif
                @endforeach

                @if (!$isPending)
                @php $nextIndex = count($rests ?? []); @endphp
                <div class="detail-card__row" id="rest-new-row" data-index="{{ $nextIndex }}">
                    <span class="detail-card__label">休憩{{ $nextIndex > 0 ? $nextIndex + 1 : '' }}</span>
                    <div class="detail-card__value">
                        <input class="detail-card__input" type="text" name="rests[{{ $nextIndex }}][start]" value="{{ old("rests.{$nextIndex}.start") }}">
                        <span class="detail-card__separator">〜</span>
                        <input class="detail-card__input" type="text" name="rests[{{ $nextIndex }}][end]" value="{{ old("rests.{$nextIndex}.end") }}">
                    </div>
                </div>
                @if ($errors->has("rests.{$nextIndex}.start") || $errors->has("rests.{$nextIndex}.end"))
                <div class="detail-card__row">
                    <span class="detail-card__label"></span>
                    <div class="detail-card__value">
                        @error("rests.{$nextIndex}.start") <span class="detail-card__error">{{ $message }}</span> @enderror
                        @error("rests.{$nextIndex}.end") <span class="detail-card__error">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif
                <div id="rest-container"></div>
                @endif

                <div class="detail-card__row">
                    <span class="detail-card__label">備考</span>
                    <div class="detail-card__value">
                        @if ($isPending)
                            <div>{{ $attendance->note ?? '' }}</div>
                        @else
                            <textarea class="detail-card__textarea" name="note">{{ old('note', $attendance->note ?? '') }}</textarea>
                        @endif
                    </div>
                </div>
                @error('note')
                <div class="detail-card__row">
                    <span class="detail-card__label"></span>
                    <div class="detail-card__value">
                        <span class="detail-card__error">{{ $message }}</span>
                    </div>
                </div>
                @enderror
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

@if (!$isPending)
@section('scripts')
<script src="{{ asset('js/rest-row.js') }}"></script>
@endsection
@endif
