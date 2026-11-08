@props(['field'])

@error($field)
    <p {{ $attributes->merge(['id' => $field.'-error', 'class' => 'field-error']) }} role="alert">{{ $message }}</p>
@enderror
