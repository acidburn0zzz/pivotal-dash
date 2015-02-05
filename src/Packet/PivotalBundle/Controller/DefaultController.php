<?php

namespace Packet\PivotalBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template("PacketPivotalBundle:Default:projects.html.twig")
     */
    public function projectsAction(Request $request)
    {
				$piv = $this->get('pivotal');
				return array('projects' => (array) $piv->getProjects(), 'offset' => $request->query->get('offset'), 'current' => $request->query->get('id'));
    }

    /**
     * @Route("/project/{id}")
     * @Template("PacketPivotalBundle:Default:project.html.twig")
     */
    public function projectAction($id, Request $request)
    {
				$piv = $this->get('pivotal');
				$offset = $request->query->get('offset');

				return array('project' => $piv->getProject($id, $offset));
    }

    /**
     * @Route("/team")
     * @Template("PacketPivotalBundle:Default:team.html.twig")
     */
    public function teamAction()
    {
				$piv = $this->get('pivotal');
				return array('users' => (array) $piv->getTeam());
    }

    /**
     * @Route("/user/{initials}")
     * @Template("PacketPivotalBundle:Default:user.html.twig")
     */
    public function userAction($initials)
    {
				$piv = $this->get('pivotal');
				if(!$user = $piv->getUser($initials)) {
					throw $this->createNotFoundException('user not found');
				}
				return array('user' => $user);
    }
}
