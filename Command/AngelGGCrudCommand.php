<?php

namespace AngelGarrido\CrudGeneratorBundle\Command;

use AngelGarrido\CrudGeneratorBundle\Generator\AngelGGCrudGenerator;
use AngelGarrido\CrudGeneratorBundle\Generator\AngelGGFormGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

class AngelGGCrudCommand extends GenerateDoctrineCrudCommand
{
    protected $generator;
    protected $formGenerator;

    protected function configure()
    {
        parent::configure();

        $this->setName('AngelGG:generate:crud');
        $this->setDescription('A personalized CRUD generator by Angel Garrido.');
    }

    protected function createGenerator($bundle = null)
    {
        $gen = new AngelGGCrudGenerator($this->getContainer()->get('filesystem'));

        return $gen;
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = $this->getContainer()->get('kernel')->locateResource('@AngelGarridoCrudGeneratorBundle/Resources/skeleton');

        return $skeletonDirs;
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new AngelGGFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }
}