<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Events\CommentCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Notifies post's author about new comments.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
class CommentNotificationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $translator;
    private $urlGenerator;
    private $sender;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, $sender)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->sender = $sender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => 'onCommentCreated',
        ];
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        /** @var Comment $comment */
        $comment = $event->getComment();
        $post = $comment->getPost();

        $linkToPost = $this->urlGenerator->generate('blog_post', [
            'slug' => $post->getSlug(),
            '_fragment' => 'comment_'.$comment->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $subject = $this->translator->trans('notification.comment_created');
        $body = $this->translator->trans('notification.comment_created.description', [
            '%title%' => $post->getTitle(),
            '%link%' => $linkToPost,
        ]);

        // See https://symfony.com/doc/current/mailer.html#creating-sending-messages
        $email = (new Email())
            ->subject($subject)
            ->to($post->getAuthor()->getEmail())
            ->from($this->sender)
            ->html($body)
        ;

        // In config/packages/dev/mailer.yaml the local 'MAILER_DSN' is set to 'null://null'.
        // That's why in the development environment you won't actually receive any email.
        // However, you can inspect the contents of those unsent emails using the debug toolbar.
        // See https://symfony.com/doc/current/mailer.html#testing-emails-in-development
        $this->mailer->send($email);
    }
}
