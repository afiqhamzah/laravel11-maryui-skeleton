<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use RuntimeException;

class MaryInstallDockerCommand extends Command
{
    protected $signature = 'mary:docker-install';

    protected $description = 'Install mary ui using docker';

    protected $ds = DIRECTORY_SEPARATOR;

    public function handle()
    {
        $this->info("❤️  maryUI installer");

        // Setup Tailwind and Daisy
         $this->setupTailwindDaisy();

        // Copy stubs if is brand-new project
        $this->copyStubs();

        // Rename components if Jetstream or Breeze are detected
        $this->renameComponents();

        // Clear view cache
        Artisan::call('view:clear');

        $this->info("\n");
        $this->info("✅  Done! Run `yarn dev` or `npm run dev`");
        $this->info("🌟  Give it a star: https://github.com/robsontenorio/mary");
        $this->info("❤️  Sponsor this project: https://github.com/sponsors/robsontenorio\n");
    }

    public function setupTailwindDaisy()
    {
        $maryUIVendorPath = base_path() . '/vendor/robsontenorio/mary';

        $cssPath = base_path() . "{$this->ds}resources{$this->ds}css{$this->ds}app.css";
        $css = File::get($cssPath);

        if (! str($css)->contains('@tailwind')) {
            $stub = File::get($maryUIVendorPath . "/stubs/app.css");
            File::put($cssPath, str($css)->prepend($stub));
        }

        /**
         * Setup tailwind.config.js
         */

        $tailwindJsPath = base_path() . "{$this->ds}tailwind.config.js";

        if (! File::exists($tailwindJsPath)) {
            $this->copyFile($maryUIVendorPath . "/stubs/tailwind.config.js", "tailwind.config.js");
            $this->copyFile($maryUIVendorPath . "/stubs/postcss.config.js", "postcss.config.js");

            return;
        }

        /**
         * Setup Tailwind plugins
         */

        $tailwindJs = File::get($tailwindJsPath);
        $pluginsBlock = str($tailwindJs)->match('/plugins:[\S\s]*\[[\S\s]*\]/');

        if ($pluginsBlock->contains('daisyui')) {
            $this->info($pluginsBlock->contains('daisyui'));
            return;
        }

        $plugins = $pluginsBlock->after('plugins')->after('[')->before(']')->squish()->trim()->remove(' ')->explode(',')->add('require("daisyui")')->filter()->implode(',');
        $plugins = str($plugins)->prepend("\n\t\t")->replace(',', ",\n\t\t")->append("\r\n\t");
        $plugins = str($tailwindJs)->replace($pluginsBlock, "plugins: [$plugins]");

        File::put($tailwindJsPath, $plugins);

        /**
         * Setup Tailwind contents
         */
        $tailwindJs = File::get($tailwindJsPath);
        $originalContents = str($tailwindJs)->after('contents')->after('[')->before(']');

        if ($originalContents->contains('robsontenorio/mary')) {
            return;
        }

        $contents = $originalContents->squish()->trim()->remove(' ')->explode(',')->add('"./vendor/robsontenorio/mary/src/View/Components/**/*.php"')->filter()->implode(', ');
        $contents = str($contents)->prepend("\n\t\t")->replace(',', ",\n\t\t")->append("\r\n\t");
        $contents = str($tailwindJs)->replace($originalContents, $contents);

        File::put($tailwindJsPath, $contents);
    }

    /**
     * If Jetstream or Breeze are detected we publish config file and add a global prefix to maryUI components,
     * in order to avoid name collision with existing components.
     */
    public function renameComponents()
    {
        $composerJson = File::get(base_path() . "/composer.json");

        collect(['jetstream', 'breeze'])->each(function (string $target) use ($composerJson) {
            if (str($composerJson)->contains($target)) {
                Artisan::call('vendor:publish --force --tag mary.config');

                $path = base_path() . "{$this->ds}config{$this->ds}mary.php";
                $config = File::get($path);
                $contents = str($config)->replace("'prefix' => ''", "'prefix' => 'mary-'");
                File::put($path, $contents);

                $this->warn('---------------------------------------------');
                $this->warn("🚨`$target` was detected.🚨");
                $this->warn('---------------------------------------------');
                $this->warn("A global prefix on maryUI components was added to avoid name collision.");
                $this->warn("\n * Example: x-mary-button, x-mary-card ...");
                $this->warn(" * See config/mary.php");
                $this->warn('---------------------------------------------');
            }
        });
    }

    /**
     * Copy example demo stub if it is a brand-new project.
     */
    public function copyStubs(): void
    {
        $maryUIVendorPath = base_path() . '/vendor/robsontenorio/mary';

        $routes = base_path() . "{$this->ds}routes";

        // If there is something in there, skip stubs
        if (count(file("$routes{$this->ds}web.php")) > 20) {
            return;
        }

        $this->info("Copying stubs...\n");

        $appViewComponents = "app{$this->ds}View{$this->ds}Components";
        $livewirePath = "app{$this->ds}Livewire";
        $layoutsPath = "resources{$this->ds}views{$this->ds}components{$this->ds}layouts";
        $livewireBladePath = "resources{$this->ds}views{$this->ds}livewire";

        // Blade Brand component
        $this->createDirectoryIfNotExists($appViewComponents);
        $this->copyFile($maryUIVendorPath . "/stubs/AppBrand.php", "{$appViewComponents}{$this->ds}AppBrand.php");

        // Default app layout
        $this->createDirectoryIfNotExists($layoutsPath);
        $this->copyFile($maryUIVendorPath . "/stubs/app.blade.php", "{$layoutsPath}{$this->ds}app.blade.php");

        // Livewire blade views
        $this->createDirectoryIfNotExists($livewireBladePath);

        // Demo component and its route
        if (true) {
            $this->createDirectoryIfNotExists("$livewireBladePath{$this->ds}users");
            $this->copyFile($maryUIVendorPath . "/stubs/index.blade.php", "$livewireBladePath{$this->ds}users{$this->ds}index.blade.php");
            $this->copyFile($maryUIVendorPath . "/stubs/web-volt.php", "$routes{$this->ds}web.php");
        } else {
            $this->createDirectoryIfNotExists($livewirePath);
            $this->copyFile($maryUIVendorPath . "/stubs/Welcome.php", "{$livewirePath}{$this->ds}Welcome.php");
            $this->copyFile($maryUIVendorPath . "/stubs/welcome.blade.php", "{$livewireBladePath}{$this->ds}welcome.blade.php");
            $this->copyFile($maryUIVendorPath . "/stubs/web.php", "$routes{$this->ds}web.php");
        }
    }

    private function createDirectoryIfNotExists(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function copyFile(string $source, string $destination): void
    {
        $source = str_replace('/', DIRECTORY_SEPARATOR, $source);
        $destination = str_replace('/', DIRECTORY_SEPARATOR, $destination);

        if (! copy($source, $destination)) {
            throw new RuntimeException("Failed to copy {$source} to {$destination}");
        }
    }
}
