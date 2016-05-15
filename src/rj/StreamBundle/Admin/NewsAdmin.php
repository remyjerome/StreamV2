<?php
// src/rj/StreamBundle/Admin/NewsAdmin.php
namespace rj\StreamBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class NewsAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('titre', 'text', array(
                'label' => 'News Titre'
            ))
            ->add('description', 'textarea')
            ->add('auteur', 'text')
            ->add('image','text')
            ->add('tag1', 'text')
            ->add('tag2', 'text')
            ->add('tag3', 'text')
            ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('date')
                ->add('auteur')
                ->add('datesort', 'doctrine_orm_datetime', array())
                ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('titre')
            ->add('description')
            ->add('auteur')
            ->add('date')
            ->add('_action', 'actions', array(
                 'actions' => array(
                 'show' => array(),
                 'edit' => array(),
            )))
            ;

    }
}