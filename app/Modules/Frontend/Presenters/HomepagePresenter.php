<?php

declare(strict_types=1);

namespace App\Modules\Frontend;

use App\Model;


final class HomepagePresenter extends \App\Presenters\BasePresenter
{
	/** @var Model\Article $article @inject */
	public $article;
	
	/** @var Model\Comment $comment @inject */
	public $comment;

	public function renderDefault() : void
	{
		$articles = $this->article->getAll();
		$this->template->articles = $articles;
		
		$comments = $this->comment->getAll();
		$this->template->comments = $comments;
	}
}

