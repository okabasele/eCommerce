<?php

namespace App\Notification;

use App\Entity\Contact;
use Twig\Environment;

class ContactNotification
{
    /**
     * @var \SwiftMailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    // hors d'un controller, on ne peut faire des injections de dépendances que dans des constructeurs
    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function notify(Contact $contact)
    {
        $message = (new \Swift_Message('Nouveau message de ' . $contact->getEmail()))   // objet du mail
        ->setFrom($contact->getEmail()) // expéditeur
        ->setTo('hello@colorlib.com') // destinataire
        ->setReplyTo($contact->getEmail())  // adresse de réponse
        ->setBody($this->renderer->render('emails/contact.html.twig', [
            'contact' => $contact
        ]), 'text/html');
        // préciser le type du corps du mail pour interpréter les balises html
        $this->mailer->send($message);
    }
}