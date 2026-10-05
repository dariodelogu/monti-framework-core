<?php

namespace App\System\Composer;

use Composer\Script\Event;

class DiscoveryScript
{
    public static function postAutoloadDump(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $baseDir = dirname($vendorDir);

        $installedJsonPath = $vendorDir . '/composer/installed.json';

        if(!is_file($installedJsonPath)) {
            return;
        }

        $data = json_decode(file_get_contents($installedJsonPath), true, flags: JSON_THROW_ON_ERROR);
        $packages = $data['packages'] ?? $data; // Composer 2.x nests packages under 'packages'

        $exclude = [];
        $classes = [];
        foreach($packages as $package) {
            if (in_array($package['name'] ?? null, $exclude, true)) {
                continue;
            }

            $classes = [
                ...$classes,
                ...($package['extra']['monti-framework']['providers'] ?? []),
            ];
        }

        $classes = array_values(array_unique($classes));

        $export = var_export($classes, true);

        $cacheDir = $baseDir . '/cache';
        if(!is_dir($cacheDir)) {
            mkdir($cacheDir, recursive: true);
        }

        file_put_contents(
            $cacheDir . '/providers.php',
            "<?php\n\n// Auto-generated file, do not edit by hand.\nreturn {$export};\n"
        );

        $event->getIO()->write(sprintf('Discovered %d provider(s).', count($classes)));
    }
}