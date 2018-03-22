<?php

namespace FieldInteractive\CitoBundle\Controller;

use FieldInteractive\CitoBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CitoController extends Controller
{
    private $pagesPath;

    public function __construct($pagesPath)
    {
        $this->pagesPath = $pagesPath;
    }

    /**
     * @Route("/contact", name="field_cito_contact")
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(ContactType::class, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $flash = '<div class="mail">'
                .'<p><b>Email:</b> '.$data['email'].'</p>'
                .'<p><b>Subject:</b> '.$data['subject'].'</p>'
                .'<p><b>Message:</b> '.$data['message'].'</p>';
            if (!empty($data['attachment'])) {
                foreach ($data['attachment'] as $key => $attachment) {
                    $flash .= '<p><b>Attachment_'.$key.':</b> '.$attachment->getClientOriginalName().'</p>';
                }
            }
            $flash .= '</div>';

            $this->addFlash('notice', $flash);

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/application", name="field_cito_application")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function applicationAction(Request $request)
    {
        if ($request->isMethod('post')) {
            $flash = '<div class="application">'
                .'<p><b>Salutation:</b> '.$request->get('salutation').'</p>'
                .'<p><b>First Name:</b> '.$request->get('first_name').'</p>'
                .'<p><b>Last Name:</b> '.$request->get('last_name').'</p>'
                .'<p><b>Birthday:</b> '.$request->get('birthday').'</p>'
                .'<p><b>Email:</b> '.$request->get('email').'</p>'
                .'<p><b>Curriculum Vitae:</b> '.$request->files->get('curriculum_vitae')->getClientOriginalName().'</p>'
                .'<p><b>Testimony:</b> '.$request->files->get('testimony')->getClientOriginalName().'</p>';
            if ($request->files->has('further_files')) {
                foreach ($request->files->get('further_files') as $key => $file) {
                    $flash .= '<p><b>File_'.$key.':</b> '.$file->getClientOriginalName().'</p>';
                }
            }
            $flash .= '</div>';

            $this->addFlash('notice', $flash);
        }

        return $this->render('application.html.twig');
    }


    /**
     * The 'CatchAllAction' every other action/route must be above!!
     *
     * @Route("/{url}", name="field_cito_z", requirements={"url": "(.+)?"})
     */
    public function zAction(Request $request, $url)
    {
        $url = rtrim($url, '/');

//        var_dump($this->pagesPath);exit;

        if (is_file($this->pagesPath.$url.'.html.twig')) {
            return $this->render($url.'.html.twig');
        } elseif (is_file($this->pagesPath.$url.'/index.html.twig')) {
            return $this->render($url.'/index.html.twig');
        }

        throw $this->createNotFoundException($url.' not found');
    }
}
