<?php

namespace Eddyter\Laravel\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'eddyter:install
                            {--force : Overwrite existing published Eddyter files}
                            {--config : Publish the Eddyter configuration file}';

    protected $description = 'Publish Eddyter Laravel assets and show integration instructions';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->components->info('Publishing Eddyter bridge assets...');

        $this->call('vendor:publish', array_filter([
            '--tag' => 'eddyter-assets',
            '--force' => $force ?: null,
        ]));

        if ($this->option('config')) {
            $this->components->info('Publishing Eddyter configuration...');

            $this->call('vendor:publish', array_filter([
                '--tag' => 'eddyter-config',
                '--force' => $force ?: null,
            ]));
        }

        $this->newLine();
        $this->components->info('Eddyter installed successfully.');
        $this->newLine();

        $this->line('  <fg=yellow>1.</> Add your API key to <fg=cyan>.env</>:');
        $this->line('     <fg=green>EDDYTER_API_KEY=your_eddyter_key</>');
        $this->newLine();

        $this->line('  <fg=yellow>2.</> Add these directives to your Blade layout:');
        $this->line('     <fg=green>@eddyterStyles</>  <comment>in &lt;head&gt;</comment>');
        $this->line('     <fg=green>@eddyterScripts</> <comment>before &lt;/body&gt;</comment>');
        $this->newLine();

        $this->line('  <fg=yellow>3.</> Use the editor in a form:');
        $this->line('     <fg=green>&lt;x-eddyter name="body" :value="$post->body ?? \'\'" /&gt;</>');
        $this->newLine();

        if (! $this->option('config')) {
            $this->line('  <comment>Optional: run</comment> <fg=green>php artisan eddyter:install --config</> <comment>to publish config/eddyter.php.</comment>');
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
