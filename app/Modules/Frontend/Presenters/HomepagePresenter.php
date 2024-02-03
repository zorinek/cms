<?php

declare(strict_types=1);

namespace App\Modules\Frontend;

use App\Model;


final class HomepagePresenter extends \App\Presenters\BasePresenter
{
	/** @var Model\Article $article @inject */
	public $article;

	public function renderDefault() : void
	{
		$articles = $this->article->getAll();
		$this->template->articles = $articles;
	}
}

