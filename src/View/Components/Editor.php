<?php

namespace Eddyter\Laravel\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use JsonException;

class Editor extends Component
{
    public string $name;

    public string $id;

    public string $editorId;

    public string $configId;

    public string $initialValue;

    public ?string $value;

    public ?string $apiKey;

    public string $height;

    public string $initConfigJson;

    public ?array $user;

    public ?string $mode;

    public ?bool $darkMode;

    public ?string $editorClass;

    public ?string $containerClass;

    public ?string $contentClass;

    public ?string $floatingToolbarClass;

    public ?array $style;

    public ?array $toolbar;

    public ?array $editorOptions;

    public ?array $defaultFontFamilies;

    public ?array $mentionUserList;

    public ?bool $enableReactNativeBridge;

    public ?string $verifyKeyUrl;

    public array $options;

    public function __construct(
        string $name,
        ?string $id = null,
        ?string $value = null,
        ?string $apiKey = null,
        ?string $height = '400px',
        ?array $user = null,
        ?string $mode = null,
        ?bool $darkMode = null,
        ?string $editorClass = null,
        ?string $containerClass = null,
        ?string $contentClass = null,
        ?string $floatingToolbarClass = null,
        ?array $style = null,
        ?array $toolbar = null,
        ?array $editorOptions = null,
        ?array $defaultFontFamilies = null,
        ?array $mentionUserList = null,
        ?bool $enableReactNativeBridge = null,
        ?string $verifyKeyUrl = null,
        array $options = [],
    ) {
        $this->name = $name;
        $this->id = $id ?: $this->generateElementId($name);
        $this->editorId = $this->id . '_editor';
        $this->configId = $this->id . '_config';
        $this->value = $value;
        $this->apiKey = $apiKey ?: config('eddyter.api_key');
        $this->height = $height ?? '400px';

        $this->user = $user;
        $this->mode = $mode;
        $this->darkMode = $darkMode;
        $this->editorClass = $editorClass;
        $this->containerClass = $containerClass;
        $this->contentClass = $contentClass;
        $this->floatingToolbarClass = $floatingToolbarClass;
        $this->style = $style;
        $this->toolbar = $toolbar;
        $this->editorOptions = $editorOptions;
        $this->defaultFontFamilies = $defaultFontFamilies;
        $this->mentionUserList = $mentionUserList;
        $this->enableReactNativeBridge = $enableReactNativeBridge;
        $this->verifyKeyUrl = $verifyKeyUrl;
        $this->options = $options;

        $this->initialValue = (string) old(
            $this->normalizeOldInputKey($name),
            $value ?? ''
        );

        $this->initConfigJson = $this->encodeInitConfig(
            $this->buildInitConfig($this->initialValue)
        );
    }

    public function render()
    {
        return view('eddyter::components.editor');
    }

    protected function buildInitConfig(string $initialContent): array
    {
        $defaults = $this->filterNullValues(config('eddyter.defaults', []));

        $fromOptions = $this->normalizeOptionsArray($this->options);

        $fromProps = $this->filterNullValues([
            'apiKey' => $this->apiKey,
            'user' => $this->user,
            'mode' => $this->mode,
            'darkMode' => $this->darkMode,
            'class' => $this->editorClass,
            'containerClass' => $this->containerClass,
            'contentClass' => $this->contentClass,
            'floatingToolbarClass' => $this->floatingToolbarClass,
            'style' => $this->style,
            'toolbar' => $this->toolbar,
            'editor' => $this->editorOptions,
            'defaultFontFamilies' => $this->defaultFontFamilies,
            'mentionUserList' => $this->mentionUserList,
            'enableReactNativeBridge' => $this->enableReactNativeBridge,
            'verifyKeyUrl' => $this->verifyKeyUrl,
        ]);

        $merged = array_replace_recursive($defaults, $fromOptions, $fromProps);

        $merged['initialContent'] = $initialContent;

        return $merged;
    }

    protected function encodeInitConfig(array $config): string
    {
        try {
            return json_encode(
                $config,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new JsonException(
                'Failed to encode Eddyter editor configuration: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    protected function normalizeOldInputKey(string $name): string
    {
        if (! str_contains($name, '[')) {
            return $name;
        }

        preg_match('/^([^\[]+)/', $name, $prefix);

        $key = $prefix[1] ?? '';

        preg_match_all('/\[([^\]]*)\]/', $name, $segments);

        foreach ($segments[1] as $segment) {
            $key .= '.' . $segment;
        }

        return $key;
    }

    protected function generateElementId(string $name): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) ?? 'field';
        $sanitized = trim($sanitized, '_');

        if ($sanitized === '') {
            $sanitized = 'field';
        }

        return 'eddyter_' . $sanitized . '_' . Str::lower(Str::random(12));
    }

    protected function normalizeOptionsArray(array $options): array
    {
        $normalized = $options;

        if (isset($normalized['editorClass']) && ! isset($normalized['class'])) {
            $normalized['class'] = $normalized['editorClass'];
            unset($normalized['editorClass']);
        }

        if (isset($normalized['verify_key_url']) && ! isset($normalized['verifyKeyUrl'])) {
            $normalized['verifyKeyUrl'] = $normalized['verify_key_url'];
            unset($normalized['verify_key_url']);
        }

        return $this->filterNullValues($normalized);
    }

    protected function filterNullValues(array $values): array
    {
        $filtered = [];

        foreach ($values as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = $this->filterNullValues($value);

                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }
}
