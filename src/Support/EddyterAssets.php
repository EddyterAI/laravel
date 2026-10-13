<?php

namespace Eddyter\Laravel\Support;

final class EddyterAssets
{
    /**
     * The @eddyter/core SDK version this package release is built against.
     * Managed by the package — not configurable by the host application.
     */
    public const SDK_VERSION = '1.0.4';

    public static function scriptUrl(): string
    {
        return sprintf(
            'https://unpkg.com/@eddyter/core@%s/dist/editor.iife.js',
            self::SDK_VERSION
        );
    }

    public static function stylesheetUrl(): string
    {
        return sprintf(
            'https://unpkg.com/@eddyter/core@%s/dist/style.css',
            self::SDK_VERSION
        );
    }

    private function __construct()
    {
    }
}
