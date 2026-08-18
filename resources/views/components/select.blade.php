@props(['label', 'name', 'options' => []])

<div>
    <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
    <select name="{{ $name }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected(old($name) === $option)>{{ $option }}</option>
        @endforeach
    </select>
</div>
