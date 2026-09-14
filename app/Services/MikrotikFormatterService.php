<?php

namespace App\Services;

class MikrotikFormatterService
{
    /**
     * Create a new class instance.
     */
public static function toCliScript(array $apiCommands): string
    {
        $lines = [];

        foreach ($apiCommands as $cmd) {
            $path   = $cmd['path'] ?? '';
            $action = $cmd['action'] ?? 'add';
            $id     = isset($cmd['id']) ? " numbers={$cmd['id']}" : '';
            
            $params = '';
            if (isset($cmd['params']) && is_array($cmd['params'])) {
                foreach ($cmd['params'] as $key => $val) {
                    $params .= " {$key}=\"{$val}\"";
                }
            }

            $lines[] = trim("{$path} {$action}{$id}{$params}");
        }

        return implode("\n", $lines);
    }
}
