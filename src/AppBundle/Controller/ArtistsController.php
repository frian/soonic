<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Unirest\Request as RestRequest;

class ArtistsController extends Controller
{
    private $api = 'http://localhost:4040/rest/';
    private $headers = array('Accept' => 'application/json');
    private $auth = array('u' => 'admin', 't' => '05fadcedc07c5ee968211ce54beff15a', 's' => 'c19b2d', 'v' => '1.12.0', 'c' => 'myapp');

    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $headers = $this->headers;

        $query = $this->auth;

        $response = RestRequest::get( $this->api . 'getArtists',$headers,$query);

        $subsonic = simplexml_load_string($response->body);

        $artistsIndex = $subsonic->artists->index;

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'artistsIndex' => $artistsIndex,
        ]);
    }
}
