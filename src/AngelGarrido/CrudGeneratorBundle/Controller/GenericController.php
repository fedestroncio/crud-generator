<?php

namespace AngelGarrido\CrudGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GenericController extends Controller
{
    protected function flashSuccessCreate(Request $request, $entity)
    {
        $this->flash($request, 'success', 'flash.entity.create', $entity);
    }

    protected function flashErrorCreate(Request $request, $entity)
    {
        $this->flash($request, 'danger', 'flash.error.create', $entity);
    }

    protected function flashSuccessDelete(Request $request, $entity)
    {
        $this->flash($request, 'success', 'flash.entity.delete', $entity);
    }

    protected function flashErrorDelete(Request $request, $entity)
    {
        $this->flash($request, 'danger', 'flash.error.delete', $entity);
    }

    protected function flash(Request $request, $type, $action, $entity)
    {
        $request->getSession()->getFlashBag()->add($type, $this->translate($action, array('%entity%' => $entity)));
    }

    protected function translate($message, $params)
    {
        return $this->get('translator')->trans($message, $params, 'AngelGarridoCrudGeneratorBundle');
    }
} 