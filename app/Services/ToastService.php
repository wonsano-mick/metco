<?php

namespace App\Services;

class ToastService
{
    public static function success($message)
    {
        self::dispatch($message, 'success');
    }

    public static function error($message)
    {
        self::dispatch($message, 'error');
    }

    public static function warning($message)
    {
        self::dispatch($message, 'warning');
    }

    public static function info($message)
    {
        self::dispatch($message, 'info');
    }

    private static function dispatch($message, $type)
    {
        if (app()->runningInConsole()) {
            return;
        }

        request()->session()->flash('toast', [
            'message' => $message,
            'type' => $type,
        ]);
    }

    public static function livewire($component, $message, $type = 'success')
    {
        $component->dispatch('showToast', message: $message, type: $type);
    }
}
