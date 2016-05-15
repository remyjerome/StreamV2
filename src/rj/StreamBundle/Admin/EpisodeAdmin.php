<?php
// src/rj/StreamBundle/Admin/EpisodeAdmin.php
namespace rj\StreamBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EpisodeAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('saison', 'integer')
            ->add('episode', 'integer')
            ->add('titre', 'text')
            ->add('description', 'textarea')
            ->add('lien', 'text')
            ->add('etat')
            ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('saison')
                ->add('episode')
                ->add('etat');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('saison', 'text')
            ->add('episode', 'text')
            ->add('titre', 'text')
            #->add('description', 'textarea')
            ->add('lien', 'url')
            ->add('etat')
            ->add('_action', 'actions', array(
                 'actions' => array(
                 'show' => array(),
                 'edit' => array(),
            )))
            ;

    }
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('lien')
        ;
    }
}