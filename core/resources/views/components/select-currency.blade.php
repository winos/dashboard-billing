@props(['title'=> null])

@php
    $label = $title ?? 'All'; 
    $currencies = \App\Models\Currency::enable()->get();
@endphp

<select name="currency_id" class="form-control select2">
    <option value="">{{ __($label) }}</option>
    @foreach($currencies as $currency)
        <option value="{{ $currency->id }}" @selected(request()->currency_id == $currency->id)>{{ $currency->currency_code }}</option>
    @endforeach
</select>