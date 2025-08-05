@php
    $userType = strtolower(@userGuard()['type']);
@endphp

@foreach ($finalData as $data)
    <div class="form-group">
        <label class="form-label">{{ __($data->name) }}</label>
        @if ($data->type == 'text')
            <input type="text" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'url')
            <input type="url" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'email')
            <input type="email" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'datetime')
            <input type="datetime-local" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'date')
            <input type="date" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'time')
            <input type="time" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'number')
            <input type="number" class="form-control form--control" name="{{ $data->label }}" value="{{ old($data->label) ?? @$data->value }}" step="any" @if ($data->is_required == 'required') required @endif>
        @elseif($data->type == 'textarea')
            <textarea class="form-control form--control" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif>{{ old($data->label) ?? @$data->value }}</textarea>
        @elseif($data->type == 'select')
            <select class="form-control form--control form-select" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif>
                <option value="">@lang('Select One')</option>
                @foreach ($data->options as $item)
                    <option value="{{ $item }}" @if (old($data->label) && $item == old($data->label)) selected
                        @elseif($item == @$data->value)
                            selected @endif>
                        {{ __($item) }}
                    </option>
                @endforeach
            </select>
        @elseif($data->type == 'checkbox')
            @foreach ($data->options as $index => $option)
                <div class="form-check">
                    <input class="form-check-input exclude" name="{{ $data->label }}[]" type="checkbox" value="{{ $option }}" id="{{ $data->label }}_{{ titleToKey($option) }}" @if (old($data->label) && $option == old($data->label)) checked
                        @elseif(in_array($option, @$data->value ?? []))
                            checked @endif>
                    <label class="form-check-label" for="{{ $data->label }}_{{ titleToKey($option) }}">{{ $option }}</label>
                </div>
            @endforeach
        @elseif($data->type == 'radio')
            @foreach ($data->options as $option)
                <div class="form-check">
                    <input class="form-check-input exclude" name="{{ $data->label }}" type="radio" value="{{ $option }}" id="{{ $data->label }}_{{ titleToKey($option) }}" @if (old($data->label) && $option == old($data->label)) checked
                    @elseif($option == @$data->value)
                        checked @endif>
                    <label class="form-check-label" for="{{ $data->label }}_{{ titleToKey($option) }}">{{ $option }}</label>
                </div>
            @endforeach
        @elseif($data->type == 'file')
            <input type="file" class="form-control form-control-lg" name="{{ $data->label }}" @if ($data->is_required == 'required') required @endif accept="@foreach (explode(',', $data->extensions) as $ext) .{{ $ext }}, @endforeach">
            <div class="justify-content-between d-flex flex-wrap">
                <pre class="text--base mt-1">@lang('Supported mimes'): {{ $data->extensions }}</pre>
                @if (@$data->value)
                    <a href="{{ route("$userType.withdraw.file.download", encrypt(getFilePath('verify') . '/' . $data->value)) }}">
                        @lang('Download Attachment')
                    </a>
                @endif
            </div>
        @endif
    </div>
@endforeach
