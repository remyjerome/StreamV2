<?php

// src/rj/StreamBundle/Controller/StreamController.php

namespace rj\StreamBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use rj\StreamBundle\Entity\Episode;
use rj\StreamBundle\Entity\User;
use rj\StreamBundle\Entity\vue;
use rj\StreamBundle\Entity\News;
use Doctrine\ORM\Query\ResultSetMapping;


class StreamController extends Controller
{
    public function nbepisodeAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:Episode');
        $qb = $repository->createQueryBuilder('e');
        $qb->select('count(e.saison)');


        $episode = $qb->getQuery()->getSingleScalarResult();
        if(!$episode)
        {
            throw $this->createNotFoundException(
          'Aucun episode'
        );
        }
        $repositoryNews = $this->getDoctrine()
            ->getRepository('rjStreamBundle:News');
        $qb2 = $repositoryNews->createQueryBuilder('n');
        $qb2->select('count(n.id)');


        $news = $qb2->getQuery()->getSingleScalarResult();
        if(!$news)
        {
            throw $this->createNotFoundException(
          'Aucune news'
        );
        }
        $response = new JsonResponse();
        return $response->setData(array('nbepisode' => $episode, 'nbnews' => $news));
    }
    public function hebergeurAction()
    {
        $hebergeur = new hebergeur(1,1,"purevid");
        $hebergeur->setLien('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($hebergeur);
        $em->flush();


    return new Response('Reussi');
    }
    public function voirplusAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $new = $em->getRepository('rjStreamBundle:News')
        ->find($id);
        if(!$new)
        {
            throw $this->createNotFoundException(
          'Aucune news'
        );
        }
        $response = new JsonResponse();
        return $response->setData(array('description' => $new->getDescription()));

    }
    public function notationAction($saison, $episode, $note)
    {
        /********** On regarde si l'utilisateur n'a pas deja voté aujourd'hui **********/
        $em = $this->getDoctrine()->getManager();
        $newUser = new User($saison,$episode);  //Création d'un nouvel utilisateur (ip(auto),saison,episode,date(auto))
        $user = $em->getRepository('rjStreamBundle:User')
        ->findOneBy(array('saison' => $saison, 'episode' => $episode, 'ip' => $newUser->getIp())); //On cherche dans la BDD si l'utilisateur a deja vote pour cet episode
        if($user)   //Si on trouve un utilisateur dans la BDD
        {

            $interval = $user->getDate()->diff($newUser->getDate());
            if((int)$interval->format('%a') >= 1)   //On regarde son dernier vote remonte a plus de 24h
            {
                $user->setDate(new \Datetime());    //+de 24h on réaffecte une nouvelle date
                $em->flush();

            }
            else    //sinon on return null l'utilisateur a deja voté
            {

                $response1 = new JsonResponse();
                return $response1->setData(array('note' => ''));
            }
        }
        else //Sinon l'utilisateur vote pour la première fois pour cet episode
        {
            $em->persist($newUser); //On enregistre donc cet utilisateur dans la BDD
            $em->flush();
        }
        /********** Application de la note **********/
        $em1 = $this->getDoctrine()->getManager();
        $episode = $em1->getRepository('rjStreamBundle:Episode')
        ->findOneBy(array('saison' => $saison, 'episode' => $episode));

        if($episode)
        {
            if($note)
            {
                $noteplus = $episode->getNbnoteplus() +1;
                $episode->setNbnoteplus($noteplus);
            }
            else
            {
                $notemoins = $episode->getNbnotemoins() +1;
                $episode->setNbnotemoins($notemoins);
            }

            $newnote = ($episode->getNbnoteplus() / ( $episode->getNbnoteplus() + $episode->getNbnotemoins() ))*100;
            $episode->setNote($newnote);
        }
        else
        {
            return null;
        }
        $em1->flush();
        $response = new JsonResponse();
        return $response->setData(array('note' => $episode->getNote(), 'nbplus' => $episode->getNbnoteplus(), 'nbmoins' => $episode->getNbnotemoins() ));

    }

    public function indexAction()
    {
        //SELECT * FROM Episode HAVING date = (SELECT MAX(date) FROM Episode)

       $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:Episode'); //Entité Episode

        $qb = $repository->createQueryBuilder('e1');
        $query1 = $qb->select($qb->expr()->max('e1.date'))
            ->from('rjStreamBundle:Episode','e2')->getQuery();
        $date = $query1->getSingleResult();

        $qb2 = $repository->createQueryBuilder('e') ;
        $query2 = $qb2->having('e.date = :date')
            ->setParameter('date', $date)
            ->getQuery();

        $episode = $query2->getSingleResult();

        if (!$episode)
        {
          throw $this->createNotFoundException(
          'Aucun episode trouvé'
          );
        }
        //SELECT * FROM Episode HAVING date = (SELECT MAX(date) FROM Episode)

       $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:News');

        $qb = $repository->createQueryBuilder('n1');
        $query1 = $qb->select($qb->expr()->max('n1.date'))
            ->from('rjStreamBundle:News','n2')->getQuery();
        $date = $query1->getSingleResult();

        $qb2 = $repository->createQueryBuilder('n') ;
        $query2 = $qb2->having('n.date = :date')
            ->setParameter('date', $date)
            ->getQuery();

        $new = $query2->getSingleResult();

        if (!$new)
        {
          throw $this->createNotFoundException(
          'Aucune news trouvée'
          );
        }


       return $this->render('rjStreamBundle:Home:index.html.twig', array('new' => $new,'episode'=>$episode));
    }
    public function saisonAction($s)
    {
      if($s<1)
      {
        $s=1;
      }
      if($s>5)
      {
        $s=5;
      }
       $episodes = $this->getDoctrine()
        ->getRepository('rjStreamBundle:Episode')
        ->findBySaison($s);

        if (!$episodes)
        {
          throw $this->createNotFoundException(
          'Aucun episode pour : s'.$s
        );
        }
       return $this->render('rjStreamBundle:Saisons:index.html.twig',array('s' => $s,'episodes'=>$episodes));
    }
    public function episodeallAction()
    {
        $s=0;
       $episodes = $this->getDoctrine()
        ->getRepository('rjStreamBundle:Episode')
        ->findAll();

        if (!$episodes)
        {
          throw $this->createNotFoundException(
          'Aucun episode'
        );
        }
       return $this->render('rjStreamBundle:Saisons:index.html.twig',array('s' => $s,'episodes'=>$episodes));
    }
    public function episodevueAction()
    {
        //SELECT * FROM Episode HAVING vue = (SELECT MAX(vue) FROM Episode)
        $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:Episode'); //Entité Episode

        $qb = $repository->createQueryBuilder('e1');

        $query1 = $qb->select($qb->expr()->max('e1.vue'))
            ->from('rjStreamBundle:Episode','e2')->getQuery();
        $vue = $query1->getSingleResult();
        $qb2 = $repository->createQueryBuilder('e') ;
        $query2 = $qb2->having('e.vue = :vue')
            ->setParameter('vue', $vue)
            ->getQuery();
        $episode = $query2->getSingleResult();

        if (!$episode)
        {
          return $this->render('rjStreamBundle:Home:index.html.twig');
        }
       return $this->render('rjStreamBundle:Episodes:index.html.twig',array('s' => $episode->getSaison(),'e'=> $episode->getEpisode(), 'episode'=>$episode));
    }
    public function episodenoteAction()
    {

        //SELECT * FROM Episode HAVING note = (SELECT MAX(note) FROM Episode)
        $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:Episode'); //Entité Episode

        $qb = $repository->createQueryBuilder('e1');

        $query1 = $qb->select($qb->expr()->max('e1.note'))
            ->from('rjStreamBundle:Episode','e2')->getQuery();
        $note = $query1->getSingleResult();
        $qb2 = $repository->createQueryBuilder('e') ;
        $query2 = $qb2->having('e.note = :note')
            ->setParameter('note', $note)
            ->getQuery();
        $episode = $query2->getSingleResult();
        if (!$episode)
        {
          throw $this->createNotFoundException(
          'Aucun episode trouvé');
        }
       return $this->render('rjStreamBundle:Episodes:index.html.twig',array('s' => $episode->getSaison(),'e'=> $episode->getEpisode(), 'episode'=>$episode));
    }
    public function episodelastAction()
    {

        //SELECT * FROM Episode HAVING date = (SELECT MAX(date) FROM Episode)

       $repository = $this->getDoctrine()
            ->getRepository('rjStreamBundle:Episode'); //Entité Episode

        $qb = $repository->createQueryBuilder('e1');
        $query1 = $qb->select($qb->expr()->max('e1.date'))
            ->from('rjStreamBundle:Episode','e2')->getQuery();
        $date = $query1->getSingleResult();

        $qb2 = $repository->createQueryBuilder('e') ;
        $query2 = $qb2->having('e.date = :date')
            ->setParameter('date', $date)
            ->getQuery();

        $episode = $query2->getSingleResult();

        if (!$episode)
        {
          throw $this->createNotFoundException(
          'Aucun episode trouvé');
        }
       return $this->render('rjStreamBundle:Episodes:index.html.twig',array('s' => $episode->getSaison(),'e'=> $episode->getEpisode(), 'episode'=>$episode));
    }
    public function episodeAction($s,$e)
    {
      if($e > 10)
      {
        $s = $s +1;
        $e=1;
      }
      if($e <= 0)
      {
        $s = $s-1;
        $e = 10;
      }
      if($s <= 0)
      {
        $s=1;
        $e=1;
      }
      if($s>11)
      {
        $s =5;
        $e =10;
      }

      /********** Recherche episode BDD et affichage **********/
        $em = $this->getDoctrine()->getManager();
        $episode = $em
        ->getRepository('rjStreamBundle:Episode')
        ->findOneBy(array('saison' => $s, 'episode' => $e));

        if (!$episode)
        {
            throw $this->createNotFoundException(
          'Aucun episode trouvé');        }
        /********** Compteur de vues **********/
        $em = $this->getDoctrine()->getManager();
        $newVue = new vue($s,$e);  //Création d'un nouvel utilisateur (ip(auto),saison,episode,date(auto))
        $vue = $em->getRepository('rjStreamBundle:vue')
        ->findOneBy(array('saison' => $s, 'episode' => $e, 'ip' => $newVue->getIp())); //On cherche dans la BDD si l'utilisateur a deja vote pour cet episode
        if($vue)   //Si on trouve un utilisateur dans la BDD
        {

            $interval = $vue->getDate()->diff($newVue->getDate());
            if((int)$interval->format('%a') >= 1)   //On regarde son dernier vote remonte a plus de 24h
            {
                $vue->setDate(new \Datetime());    //+de 24h on réaffecte une nouvelle date
                $em->flush();

                $episode->setVue($episode->getVue()+1);

            }
        }
        else //Sinon l'utilisateur vote pour la première fois pour cet episode
        {
            $em->persist($newVue); //On enregistre donc cet utilisateur dans la BDD
            $em->flush();
            $episode->setVue($episode->getVue()+1);
        }
        /**************************************/
        $em->flush();
       return $this->render('rjStreamBundle:Episodes:index.html.twig',array('s' => $s,'e'=> $e, 'episode'=>$episode));
    }
    public function newsAction()
    {
        $news = $this->getDoctrine()
        ->getRepository('rjStreamBundle:News')
        ->findAll();

        if (!$news)
        {
          throw $this->createNotFoundException(
          'Aucune news'
        );
        }
       return $this->render('rjStreamBundle:News:index.html.twig', array('news'=>$news));
    }



}
