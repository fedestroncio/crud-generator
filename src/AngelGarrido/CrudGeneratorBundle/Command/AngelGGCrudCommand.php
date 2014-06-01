<?php

namespace AngelGarrido\CrudGeneratorBundle\Command;

use AngelGarrido\CrudGeneratorBundle\Generator\AngelGGCrudGenerator;
use AngelGarrido\CrudGeneratorBundle\Generator\AngelGGFormGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Console\Input\InputOption;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

class AngelGGCrudCommand extends GenerateDoctrineCrudCommand
{
    protected $generator;
    protected $formGenerator;
    protected $template;

    protected function configure()
    {
        parent::configure();

        $this->setName('AngelGG:generate:crud');
        $this->setDescription('A personalized CRUD generator by Angel Garrido.');
        $this->addOption('template', '', InputOption::VALUE_OPTIONAL, 'The full name of the template to extend, defaults to ::base.html.twig if blank.');
    }

    protected function createGenerator($bundle = null)
    {
        $gen = new AngelGGCrudGenerator($this->getContainer()->get('filesystem'));
        $gen->setTemplate($this->template);

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

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        parent::interact($input, $output);

        $template = $input->getOption('template');
        $output->writeln(array(
            '',
            'The name of the template from which you want the generated twigs to extend.',
            'Dont need to write the extension (".html.twig").',
            '(e.g. AngelGarridoCrudGeneratorBundle:base:layout)',
            '',
        ));
        $template = $dialog->ask($output, $dialog->getQuestion('template name', $template));
        $input->setOption('template', $template);
        $this->template = $input->getOption('template');
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