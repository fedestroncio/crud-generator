<?php

namespace AngelGarrido\CrudGeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AngelGGCrudGenerator extends DoctrineCrudGenerator
{
    protected $formFilterGenerator;

    protected $template;

    public function generate(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite)
    {
        parent::generate($bundle, $entity, $metadata, $format, $routePrefix, $needWriteActions, $forceOverwrite);

        $this->generateFormFilter($bundle, $entity, $metadata, $forceOverwrite);
    }

    /**
     * Generates the entity form class if it does not exist.
     *
     * @param BundleInterface $bundle The bundle in which to create the class
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity metadata class
     * @param $forceOverwrite
     *
     * @throws \RuntimeException
     */
    public function generateFormFilter(BundleInterface $bundle, $entity, ClassMetadataInfo $metadata, $forceOverwrite)
    {
        $parts       = explode('\\', $entity);
        $entityClass = array_pop($parts);

        $this->className = $entityClass.'FilterType';
        $dirPath         = $bundle->getPath().'/Form';
        $this->classPath = $dirPath.'/'.str_replace('\\', '/', $entity).'FilterType.php';

        if (!$forceOverwrite && file_exists($this->classPath)) {
            throw new \RuntimeException(sprintf('Unable to generate the %s form class as it already exists under the %s file', $this->className, $this->classPath));
        }

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The form generator does not support entity classes with multiple primary keys.');
        }

        $parts = explode('\\', $entity);
        array_pop($parts);

        $this->renderFile('form/FormFilterType.php.twig', $this->classPath, array(
            'fields_data'      => $this->getFieldsDataFromMetadata($metadata),
            'namespace'        => $bundle->getNamespace(),
            'entity_namespace' => implode('\\', $parts),
            'entity_class'     => $entityClass,
            'bundle'           => $bundle->getName(),
            'form_class'       => $this->className,
            'form_filter_type_name'   => strtolower(str_replace('\\', '_', $bundle->getNamespace()).($parts ? '_' : '').implode('_', $parts).'_'.$this->className),
        ));
    }

    public function getFilterType($dbType, $columnName)
    {
        switch ($dbType) {
            case 'boolean':
                return 'filter_checkbox';
            case 'datetime':
            case 'vardatetime':
            case 'datetimetz':
                return 'filter_date_range';
            case 'date':
                return 'filter_date_range';
                break;
            case 'decimal':
            case 'float':
            case 'integer':
            case 'bigint':
            case 'smallint':
                return 'filter_number_range';
                break;
            case 'string':
            case 'text':
                return 'filter_text';
                break;
            case 'time':
                return 'filter_text';
                break;
            case 'entity':
            case 'collection':
                return 'filter_entity';
                break;
            case 'array':
                throw new \Exception('The dbType "'.$dbType.'" is only for list implemented (column "'.$columnName.'")');
                break;
            case 'virtual':
                throw new \Exception('The dbType "'.$dbType.'" is only for list implemented (column "'.$columnName.'")');
                break;
            default:
                throw new \Exception('The dbType "'.$dbType.'" is not yet implemented (column "'.$columnName.'")');
                break;
        }
    }

    /**
     * Returns an array of fields data (name and filter widget to use).
     * Fields can be both column fields and association fields.
     *
     * @param ClassMetadataInfo $metadata
     * @return array $fields
     */
    private function getFieldsDataFromMetadata(ClassMetadataInfo $metadata)
    {
        $fieldsData = (array) $metadata->fieldMappings;

        // Convert type to filter widget
        foreach ($fieldsData as $fieldName => $data) {
            $fieldsData[$fieldName]['fieldName'] = $fieldName;
            $fieldsData[$fieldName]['filterWidget'] = $this->getFilterType($fieldsData[$fieldName]['type'], $fieldName);
        }

        return $fieldsData;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateIndexView($dir)
    {
        $this->renderFile('crud/views/index.html.twig.twig', $dir.'/index.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'entity'            => $this->entity,
                'fields'            => $this->metadata->fieldMappings,
                'actions'           => $this->actions,
                'record_actions'    => $this->getRecordActions(),
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'template'          => $this->template,
            ));
    }

    /**
     * Generates the show.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateShowView($dir)
    {
        $this->renderFile('crud/views/show.html.twig.twig', $dir.'/show.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'entity'            => $this->entity,
                'fields'            => $this->metadata->fieldMappings,
                'actions'           => $this->actions,
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'template'          => $this->template,
            ));
    }

    /**
     * Generates the new.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateNewView($dir)
    {
        $this->renderFile('crud/views/new.html.twig.twig', $dir.'/new.html.twig', array(
                'bundle'            => $this->bundle->getName(),
                'entity'            => $this->entity,
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'actions'           => $this->actions,
                'template'          => $this->template,
            ));
    }

    /**
     * Generates the edit.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    protected function generateEditView($dir)
    {
        $this->renderFile('crud/views/edit.html.twig.twig', $dir.'/edit.html.twig', array(
                'route_prefix'      => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'entity'            => $this->entity,
                'bundle'            => $this->bundle->getName(),
                'actions'           => $this->actions,
                'template'          => $this->template,
            ));
    }
} 