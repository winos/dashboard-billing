@props(['strUpper' => true,  'title'=> null])

@php
    $userTypes = ['USER', 'AGENT', 'MERCHANT'];
    $label = 'All'; 

    if(!$strUpper){
        $userTypes = array_map('strtolower', $userTypes); 
    }

    if($title){
        $label = $title; 
    }
@endphp


<select name="user_type" class="form-control select2" data-minimum-results-for-search="-1">
    <option value="">{{ __($label) }}</option>
    @foreach($userTypes as $userType)
        <option value="{{ $userType }}" @selected(request()->user_type == $userType)>{{ ucfirst(strtolower($userType)) }}</option>
    @endforeach
</select>