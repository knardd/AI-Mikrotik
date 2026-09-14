<?php

namespace App\Services;

class RscParserService
{
    /**
     * Create a new class instance.
     */
    protected array $allowedPrefixes = [
        '/ip address',
        '/ip dhcp-client',
        '/ip dhcp-server',
        '/ip route',
        '/ip dns',
        '/ip firewall filter',
        '/ip firewall nat',
        '/ip firewall mangle',
        '/ip firewall raw',
        '/ip pool',
        // '/ip service',
        '/queue simple',
        '/queue tree',
        '/interface bridge',
        '/interface bridge port',
        '/interface bridge vlan',
        '/interface vlan',
        '/interface wireless',
        '/interface wifi',
    ];

    public function parseAndClean(string $content): string
    {
        $lines = explode("\n", $content);
        $cleanLines = [];
        $isRelevantSection = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if (empty($trimmed) || str_starts_with($trimmed, '#')) {
                continue;
            }

            // Sanitasi password
            $trimmed = preg_replace('/password="[^"]+"/', 'password="***"', $trimmed);

            // Cek apakah baris diawali oleh tab yang sering digunakan
            if (str_starts_with($trimmed, '/')) {
                $isRelevantSection = false;
                foreach ($this->allowedPrefixes as $prefix) {
                    if (str_starts_with($trimmed, $prefix)) {
                        $isRelevantSection = true;
                        break;
                    }
                }
            }

            if ($isRelevantSection) {
                $cleanLines[] = $trimmed;
            }
        }

        return implode("\n", $cleanLines);
    }
}
