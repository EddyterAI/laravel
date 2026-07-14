# eddyter/laravel

[![Packagist Version](https://img.shields.io/packagist/v/eddyter/laravel.svg)](https://packagist.org/packages/eddyter/laravel)

Official Laravel Blade integration for the [Eddyter](https://eddyter.com) AI rich text editor — built on [Lexical](https://lexical.dev/) with form binding, dark mode support, and API key authentication.

Drop a full-featured rich text editor into any Blade form with a single component. No React, Vite, or Node.js required in your Laravel app.

![Eddyter Editor](https://dsckj64r0usqg.cloudfront.net/assets/seo-screen.png)

## Resources

- [Docs](https://eddyter.com/docs) — Full API reference and integration guides
- [What is Eddyter? Why Developers Are Switching to This AI Editor (2026)](https://youtu.be/oNHBa-DImZc) — YouTube
- [Integrate Eddyter in 30 Minutes Using AI Tools Cursor, Claude, Lovable](https://youtu.be/5lTjRFjUWgs) — YouTube

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.1+ |
| Laravel | 10.x, 11.x, 12.x, 13.x |
| Browsers | Evergreen (Chrome, Edge, Firefox, Safari) |

The editor SDK is loaded automatically from a version pinned by this package — you never manage SDK versions, CDN URLs, or asset files yourself.

## Installation

```bash
composer require eddyter/laravel
php artisan eddyter:install
```

That's it. The install command publishes the required bridge script to `public/vendor/eddyter/`.

## Quick Start

### 1. Get your API key

1. Create an account at [eddyter.com](https://www.eddyter.com/)
2. Navigate to [License Keys](https://www.eddyter.com/user/license-key) in your dashboard
3. Copy your API key

### 2. Add the key to `.env`

```env
EDDYTER_API_KEY=your_eddyter_key
```

This key is passed to the browser and validated automatically by Eddyter.

### 3. Add the directives to your layout

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>

    @eddyterStyles
</head>
<body>
    @yield('content')

    @eddyterScripts
</body>
</html>
```

- `@eddyterStyles` — editor stylesheet, place in `<head>`
- `@eddyterScripts` — editor SDK + Laravel bridge, place before `</body>`

Both directives are duplicate-safe; calling them more than once will not output duplicate tags.

### 4. Use the editor in a form

```blade
<form method="POST" action="{{ route('posts.store') }}">
    @csrf

    <x-eddyter
        name="body"
        :value="$post->body ?? ''"
    />

    <button type="submit">Save</button>
</form>
```

The editor synchronizes its HTML into a hidden textarea, so standard Laravel form submission just works.

### 5. Validate in your controller

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'body' => ['required', 'string'],
    ]);

    Post::create($validated);

    return redirect()->route('posts.index');
}
```

## Usage Examples

### Edit form with existing content

```blade
<x-eddyter
    name="body"
    :value="old('body', $post->body)"
    height="500px"
/>
```

Validation old input is repopulated automatically — nested names included.

### Nested field names

```blade
<x-eddyter name="post[body]" :value="$post->body ?? ''" />

<x-eddyter name="sections[0][content]" :value="$sections[0]['content'] ?? ''" />
```

Laravel receives normal nested request data (`$request->input('post.body')`).

### Multiple editors on one page

```blade
<x-eddyter name="summary" height="200px" />
<x-eddyter name="body" height="500px" />
```

Each editor gets a unique auto-generated ID.

### With authenticated user (comments & mentions)

```blade
<x-eddyter
    name="body"
    :value="old('body', $post->body ?? '')"
    :user="[
        'id' => (string) auth()->id(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email,
    ]"
    :mention-user-list="$teamMembers->pluck('name')->all()"
/>
```

### Preview mode (read-only)

```blade
<x-eddyter
    name="body"
    :value="$post->body"
    mode="preview"
/>
```

### Dark mode

```blade
<x-eddyter name="body" :dark-mode="true" />
```

Omit the prop to auto-detect a `.dark` class on `<html>` or `<body>`.

### Livewire

Wrap the editor in `wire:ignore` so Livewire re-renders don't destroy it:

```blade
<div wire:ignore>
    <x-eddyter name="body" :value="$body" />
</div>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string` | **required** | Form field name (supports `post[body]` notation) |
| `id` | `string` | auto-generated | Textarea ID |
| `value` | `string` | `null` | Initial HTML content |
| `api-key` | `string` | `config('eddyter.api_key')` | Overrides the global API key |
| `height` | `string` | `400px` | Minimum editor height |
| `user` | `array` | `null` | Current user (`id`, `name`, `email`, `avatar`) |
| `mode` | `string` | `null` | `edit` or `preview` |
| `dark-mode` | `bool` | `null` | Explicit dark mode; omit to auto-detect |
| `editor-class` | `string` | `null` | Class on the editor wrapper |
| `container-class` | `string` | `null` | Class on the preview container |
| `content-class` | `string` | `null` | Class on the editable content area |
| `floating-toolbar-class` | `string` | `null` | Class on the floating toolbar |
| `style` | `array` | `null` | Inline styles |
| `toolbar` | `array` | `null` | `['mode' => 'sticky'\|'static', 'offset' => 20, 'zIndex' => 1000]` |
| `editor-options` | `array` | `null` | `['maxHeight' => '600px']` |
| `default-font-families` | `array` | `null` | Font selector list |
| `mention-user-list` | `array` | `null` | Names for `@mention` suggestions |
| `enable-react-native-bridge` | `bool` | `null` | RN WebView bridge |
| `verify-key-url` | `string` | `null` | Optional custom key verification URL |
| `options` | `array` | `[]` | Catch-all for any SDK option |

Extra HTML attributes (e.g. `class`, `wire:ignore`) are forwarded to the editor container.

## Configuration

The only environment variable you need:

```env
EDDYTER_API_KEY=your_eddyter_key
```

### Global editor defaults (optional)

Publish the config file:

```bash
php artisan eddyter:install --config
```

Then set defaults in `config/eddyter.php` — applied to every editor, overridable per component:

```php
return [
    'api_key' => env('EDDYTER_API_KEY'),

    'defaults' => [
        'toolbar' => [
            'mode' => 'sticky',
            'offset' => 64,
        ],
        'mentionUserList' => ['Alice', 'Bob'],
    ],
];
```

Config precedence (lowest to highest): global `defaults` → `:options` array → explicit component props.

## JavaScript API

After `@eddyterScripts` loads, `window.EddyterLaravel` is available:

```js
EddyterLaravel.initAll();                          // init dynamically added editors
EddyterLaravel.init(rootElementOrId);              // init one editor
EddyterLaravel.getInstance(rootElementOrId);       // get SDK instance
EddyterLaravel.update(rootElementOrId, options);   // e.g. { darkMode: true }
EddyterLaravel.destroy(rootElementOrId);           // unmount an editor
```

Call `EddyterLaravel.initAll()` after injecting new HTML (Turbo, HTMX, AJAX fragments).

### Custom events

All events bubble from the editor root element:

| Event | Detail |
|-------|--------|
| `eddyter:ready` | `{ instance }` |
| `eddyter:change` | `{ html, instance }` |
| `eddyter:focus` | `{ instance }` |
| `eddyter:blur` | `{ instance }` |
| `eddyter:height-change` | `{ height, instance }` |
| `eddyter:auth-success` | `{ instance }` |
| `eddyter:auth-error` | `{ error, instance }` |
| `eddyter:preview-click` | `{ instance }` |

```js
document.addEventListener('eddyter:change', (e) => {
  console.log('HTML:', e.detail.html);
});
```

## Advanced: Optional Key Verification

By default, Eddyter validates `EDDYTER_API_KEY` automatically — no Laravel route needed and nothing to configure.

If your application wants its own pre-verification endpoint, pass `verify-key-url`:

```blade
<x-eddyter
    name="body"
    verify-key-url="{{ route('custom.key.check') }}"
/>
```

Your route must return:

```json
{ "success": true, "message": "Verified" }
```

The request includes the CSRF token when a `<meta name="csrf-token">` tag is present. This package does **not** ship a verification route or controller.

Alternatively, register a global JavaScript handler (takes precedence over `verify-key-url`):

```js
EddyterLaravel.registerVerifyKeyHandler(async (apiKey) => {
  const response = await fetch('/api/verify-key', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ apiKey }),
  });

  return response.json();
});
```

## Which package should I use?

| Stack | Package |
|-------|---------|
| Laravel Blade (server-rendered forms) | **`eddyter/laravel`** (this package) |
| Laravel + Inertia + Vue | [`@eddyter/vue`](https://www.npmjs.com/package/@eddyter/vue) |
| Laravel + Inertia + React | [`eddyter`](https://www.npmjs.com/package/eddyter) |
| Vue / Nuxt | [`@eddyter/vue`](https://www.npmjs.com/package/@eddyter/vue) |
| Svelte / SvelteKit | [`@eddyter/svelte`](https://www.npmjs.com/package/@eddyter/svelte) |
| Angular | [`@eddyter/angular`](https://www.npmjs.com/package/@eddyter/angular) |

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| Editor area is blank | Run `php artisan eddyter:install` and confirm `@eddyterScripts` is in your layout |
| Toolbar looks unstyled | Confirm `@eddyterStyles` is in `<head>` |
| Console: "Eddyter SDK is not loaded" | `@eddyterScripts` must be in the layout that renders the editor |
| Form submits empty content | Don't remove the hidden textarea; check the browser console for init errors |
| Old input not repopulating | Use `:value="old('body', $post->body ?? '')"` |
| Editor destroyed by Livewire update | Wrap the component in `wire:ignore` |
| API key errors | Check `EDDYTER_API_KEY` in `.env`, then run `php artisan config:clear` |

## License

Eddyter is proprietary software.

- Commercial use requires a paid license

For commercial licensing, visit [eddyter.com](https://www.eddyter.com/).
