<?php

namespace Mingburnu\RepositoryIdeHelper\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;
use stdClass;

class GenerateCommand extends GeneratorCommand
{
    protected $signature   = 'ide-helper:repository';
    protected $description = 'Generate autocompletion for repository';
	
	protected function getStub() {return '';}

    public function handle()
    {
        try {
            $path    = base_path('_ide_helper_repositories.php');
            
			$br = PHP_EOL;
            $content = '<?php' . $br;
            $c = Container::getInstance();
            collect($c->getBindings())->sortKeys()->each(function (array $values, $interface) use (&$content, $br, $c) {
                if (interface_exists($interface) && collect(class_implements($interface))->contains(RepositoryInterface::class)) {
                    $concrete = $c->build($values['concrete']);
                    $repository_class = collect(explode('\\', $interface))->last();
                    $repository_namespace = Str::replaceLast("\\$repository_class", '', $interface);

                    if (is_subclass_of($concrete, BaseRepository::class) && collect(class_implements($concrete))->contains($interface)) {
                        $full_eloquent_class = $concrete->model();
                        $full_repository_eloquent_class = get_class($concrete);
                        $repository_eloquent_class = collect(explode('\\', $full_repository_eloquent_class))->last();
                        $repository_eloquent_namespace = Str::replaceLast("\\$repository_eloquent_class", '', $full_repository_eloquent_class);
                        $ancestors = $this->getAncestors($full_repository_eloquent_class);
                        $parent = $ancestors === false ? stdClass::class : $ancestors;

                        $content = $content . "namespace $repository_eloquent_namespace {
/**
 * Class $repository_eloquent_class
 * @package $repository_eloquent_namespace
 * @mixin \\$full_eloquent_class
 */
	abstract class $repository_eloquent_class extends $parent {}
}$br" . "namespace $repository_namespace {
/**
 * Class $repository_class
 * @package $repository_namespace
 * @mixin \\$full_repository_eloquent_class
 */
	abstract class $repository_class {}
}$br";
                }
            }
        });

            $this->files->put($path, $content);

            $this->info('Repository ide helper created.[' . $path . ']');
        } catch (\Exception $e) {
            $this->warn($e->getMessage());
        }
    }


    function getAncestors(string $reference, int $depth = 0): bool|string
    {
        $parent = get_parent_class($reference);

        if (is_bool($parent) || $depth === 0) {
            return $parent === false ? false : "\\$parent";
        } else {
            return "\\$parent|" . $this->getAncestors($parent, $depth - 1);
        }
    }
}
