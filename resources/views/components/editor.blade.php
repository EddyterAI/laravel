<textarea
    id="{{ $id }}"
    name="{{ $name }}"
    hidden
>{{ $initialValue }}</textarea>

<div
    id="{{ $editorId }}"
    data-eddyter-root
    data-eddyter-textarea-id="{{ $id }}"
    data-eddyter-config-id="{{ $configId }}"
    style="min-height: {{ $height }};"
    {{ $attributes }}
></div>

<script type="application/json" id="{{ $configId }}">{!! $initConfigJson !!}</script>
