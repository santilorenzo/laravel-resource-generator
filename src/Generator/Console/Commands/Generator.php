<?php

namespace Santilorenzo\Generator\Console\Commands;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Console\Command;

class Generator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the files for a new resource';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $name = $this->ask('What is the name of the resource?');

        $plural = Inflector::pluralize($name);

        $viewsPath = getcwd() . '/resources/views';
        $templatesPath = getcwd() . '/storage/app/generator_templates';

        $destDirectory = $viewsPath.'/'.$plural;
        $destPartials = $viewsPath.'/'.$plural.'/partials';

        //make the new resource directory
        mkdir($destDirectory, 0777);
        mkdir($destPartials, 0777);
        //exec("cp -R $templatesPath" . '/partials' . " $viewsPath" . '/' . $plural);

        // Blade files
        $indexBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/index.blade.php'), $name, $plural);
        $editBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/edit.blade.php'), $name, $plural);
        $createBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/create.blade.php'), $name, $plural);
        $actionsBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/partials/actions.blade.php'), $name, $plural);
        $formBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/partials/form.blade.php'), $name, $plural);
        $titleBlade = $this->replacePlaceholder(file_get_contents($templatesPath . '/partials/title.blade.php'), $name, $plural);

        if ($this->confirm('Does the resource have an image? [yes|no]')) {
            $imageTemplate = $this->replacePlaceholder(file_get_contents($templatesPath . '/image.blade.php'), $name, $plural);

            $formBlade = str_replace('<Image />', $imageTemplate, $formBlade);
        }

        file_put_contents($destDirectory.'/index.blade.php', $indexBlade);
        file_put_contents($destDirectory.'/edit.blade.php', $editBlade);
        file_put_contents($destDirectory.'/create.blade.php', $createBlade);
        file_put_contents($destPartials.'/actions.blade.php', $actionsBlade);
        file_put_contents($destPartials.'/form.blade.php', $formBlade);
        file_put_contents($destPartials.'/title.blade.php', $titleBlade);


        // JS Files
        $jsPath = getcwd() . '/resources/assets/js';

        $resourcesJs = $this->replacePlaceholder(file_get_contents($templatesPath . '/resources.js'), $name, $plural);
        $resourceJs = $this->replacePlaceholder(file_get_contents($templatesPath . '/resource.js'), $name, $plural);

        while ($this->confirm("Do you want to add components to the js path? ($jsPath) [yes|no]")) {
            $component = $this->ask('What is the name of the component?');

            $jsPath = $jsPath . "/$component";
        }

        file_put_contents($jsPath."/$plural.js", $resourcesJs);
        file_put_contents($jsPath."/$name.js", $resourceJs);



    }

    private function replacePlaceholder($fileContent, $name, $plural)
    {

        $fileContent = str_replace('#PLURAL#', $plural, $fileContent);
        $fileContent = str_replace('#NAME#', $name, $fileContent);
        $fileContent = str_replace('#CAPITALIZED#', ucfirst($name), $fileContent);

        return $fileContent;
    }
}
