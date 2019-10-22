<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Document;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlManager {
	private $manager;

	/**
	 * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
	 */
	private $urlGenerator;

	/**
	 * @var \Gaufrette\Filesystem $filesystem
	 */
	private $filesystem;

	public function __construct ( ObjectManager $manager, UrlGeneratorInterface $urlGenerator ) {
		$this->manager      = $manager;
		$this->urlGenerator = $urlGenerator;
	}

	public function userUrlFromId ( $id ) {
		return $this->urlGenerator->generate( 'member', [ 'user_id' => $id ] );
	}

	public function usergroupUrlFromId ( $id ) {
		$group = $this->manager->getRepository( Usergroup::class )->findOneBy( [ 'id' => $id ] );

		return $this->urlGenerator->generate( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
	}

	public function pageUrlFromId ( $id ) {
		$page = $this->manager->getRepository( Page::class )->findOneBy( [ 'id' => $id ] );

		return $this->urlGenerator->generate( 'group_page_index', [
				'groupSlug' => $page->getUsergroup()->getSlug(),
				'pageSlug'  => $page->getSlug(),
		] );
	}

	public function articleUrlFromId ( $id ) {
		$article = $this->manager->getRepository( Article::class )->findOneBy( [ 'id' => $id ] );

		return $this->urlGenerator->generate( 'group_article_index', [
				'groupSlug'   => $article->getUsergroup()->getSlug(),
				'articleSlug' => $article->getSlug(),
		] );
	}

	public function documentUrlFromId ( $id ) {
		$doc = $this->manager->getRepository( Document::class )->findOneBy( [ 'id' => $id ] );

		return $this->urlGenerator->generate( 'group_document_get', [
				'groupSlug'  => $doc->getUsergroup()->getSlug(),
				'documentId' => $id,
		] );
	}
}
