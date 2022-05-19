<?php

namespace Mass6\FlexibleDTO\Validation;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation;

class ValidatorFactory
{
    private Factory $factory;

    public function __construct()
    {
        $this->factory = new Validation\Factory($this->loadTranslator());
    }

    protected function loadTranslator()
    {
        $filesystem = new Filesystem();
        $loader = new FileLoader($filesystem, dirname(dirname(__FILE__)).'/lang');
        $loader->addNamespace('lang', dirname(dirname(__FILE__)).'/lang');
        $loader->load('en', 'validation', 'lang');

        return new Translator($loader, 'en');
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->factory, $method], $args);
    }
}
