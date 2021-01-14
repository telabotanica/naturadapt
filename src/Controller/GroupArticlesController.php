<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Usergroup;
use App\Form\ArticleType;
use App\Security\GroupArticleVoter;
use App\Security\GroupVoter;
use App\Service\FileManager;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupArticlesController extends AbstractController {
	/**************************************************
	 * ARTICLES
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/articles", name="group_articles_index")
	 * @param                                            $groupSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupArticlesIndex (
			$groupSlug,
            EntityManagerInterface $manager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		return $this->render( 'pages/article/articles-index.html.twig', [
				'group' => $group,
		] );
	}

	/**
	 * @Route("/articles", name="articles")
	 */
	public function articlesIndex() {
		return $this->redirectToRoute('group_articles_index', ['groupSlug' => 'communaute']);
	}

	/**************************************************
	 * ARTICLE
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/articles/new", name="group_article_new")
	 * @param                                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \App\Service\SlugGenerator                                 $slugGenerator
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupArticleNew (
			$groupSlug,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager,
			SlugGenerator $slugGenerator,
			UrlGeneratorInterface $router
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupArticleVoter::CREATE, $group );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$article = new Article();
		$form    = $this->createForm( ArticleType::class, $article );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$article->setAuthor( $this->getUser() );
			$article->setUsergroup( $group );
			$article->setCreatedAt( new DateTime() );
			$article->setSlug( $slugGenerator->generateSlug( $article->getTitle(), Article::class, 'slug', [ 'usergroup' => $group ] ) );

			$manager->persist( $article );

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				$article->setCover( $file );
			}
			// --

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::ARTICLE_CREATE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'article' => $article->getId(), 'title' => $article->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.article.article_created' );

			return $this->redirectToRoute( 'group_article_index', [ 'groupSlug' => $group->getSlug(), 'articleSlug' => $article->getSlug() ] );
		}

		return $this->render( 'pages/article/article-create.html.twig', [
				'group'   => $group,
				'article' => $article,
				'form'    => $form->createView(),
				'upload'  => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/articles/{articleSlug}/edit", name="group_article_edit")
	 * @param                                                            $groupSlug
	 * @param                                                            $articleSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 *
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupArticleEdit (
			$groupSlug,
			$articleSlug,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager,
			UrlGeneratorInterface $router
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		/**
		 * @var \App\Entity\Article $article
		 */
		$article = $manager->getRepository( Article::class )
						   ->findOneBy( [ 'usergroup' => $group, 'slug' => $articleSlug ] );

		if ( !$article ) {
			throw $this->createNotFoundException( 'The article does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupArticleVoter::EDIT, $article );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$form = $this->createForm( ArticleType::class, $article );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$article->setEditedAt( new DateTime() );

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				if ( !empty( $article->getCover() ) ) {
					$fileManager->deleteFile( $article->getCover() );
				}
				$article->setCover( $file );
			}
			// --

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::ARTICLE_EDIT );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'article' => $article->getId(), 'title' => $article->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.article.article_updated' );

			return $this->redirectToRoute( 'group_article_index', [ 'groupSlug' => $group->getSlug(), 'articleSlug' => $article->getSlug() ] );
		}

		return $this->render( 'pages/article/article-edit.html.twig', [
				'group'   => $group,
				'article' => $article,
				'form'    => $form->createView(),
				'upload'  => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/articles/{articleSlug}", name="group_article_index")
	 * @param                                            $groupSlug
	 * @param                                            $articleSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupArticleIndex (
			$groupSlug,
			$articleSlug,
            EntityManagerInterface $manager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		/**
		 * @var \App\Entity\Article $article
		 */
		$article = $manager->getRepository( Article::class )
						   ->findOneBy( [ 'usergroup' => $group, 'slug' => $articleSlug ] );

		if ( !$article ) {
			throw $this->createNotFoundException( 'The article does not exist' );
		}

		if ( !$this->isGranted( GroupArticleVoter::READ, $article ) ) {
			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/article/article-index.html.twig', [
				'group'   => $group,
				'article' => $article,
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/articles/{articleSlug}/delete", name="group_article_delete")
	 * @param                                            $groupSlug
	 * @param                                            $articleSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupArticleDelete (
			$groupSlug,
			$articleSlug,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		/**
		 * @var \App\Entity\Article $article
		 */
		$article = $manager->getRepository( Article::class )
						   ->findOneBy( [ 'usergroup' => $group, 'slug' => $articleSlug ] );

		if ( !$article ) {
			throw $this->createNotFoundException( 'The article does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupArticleVoter::DELETE, $article );

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			if ( !empty( $article->getCover() ) ) {
				$fileManager->deleteFile( $article->getCover() );
			}

			$manager->remove( $article );

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::ARTICLE_DELETE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'article' => $article->getId(), 'title' => $article->getTitle() ] );
			$manager->persist( $log );

			// --

			$manager->flush();

			$this->addFlash( 'notice', 'messages.article.article_deleted' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}
}
