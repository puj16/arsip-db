@props(['label', 'name', 'type' => 'text', 'required' => false, 'placeholder' => null])

<div>
    <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        @if($required) required @endif
        placeholder="{{ $placeholder }}"
        value="{{ old($name) }}"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
</div>
