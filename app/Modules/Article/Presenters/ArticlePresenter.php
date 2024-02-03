<?php

declare(strict_types=1);

namespace App\Modules\Article;

use App\Forms;
use App\Model;
use App\Exceptions;
use Nette\Application\UI\Form;

final class ArticlePresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\NewArticleFormFactory $newArticleFactory @inject */
	public $newArticleFactory;
	
	/** @var Model\Article $article @inject */
	public $article;

	/**
	 * Renders new article in app and provides api /artcicles/new
	 * 
	 */
	public function renderNew() : void
	{
		if($this->getHttpRequest()->getHeader("content-type") !== null && $this->getHttpRequest()->getHeader("content-type") == "application/json")
		{
			$json = $this->getHttpRequest()->getRawBody();
			$json_obj = json_decode($json);
			$return = $this->article->processJson($json_obj);
			
			if($return["status"] == "fail")
			{
				$this->getHttpResponse()->setCode(\Nette\Http\IResponse::S400_BadRequest);
			}
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($return));
		}
	}
	
	/**
	 * Renders detail of article in app and provides api /artcicles/<art_id>
	 * 
	 * @param string $art_id id of article
	 * @param string|bool $type if param is "json" it return json with all articles - api
	 */
	public function renderDetail(string $art_id, string|bool $type = false) : void
	{
		$article = $this->article->get((int)$art_id);
		if($type == "json")
		{
			$json = $this->article->prepareResponseJsonOne($article);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($json));
		}
		
		$this->template->article = $article;
	}
	
	/**
	 * Renders overview of all articles in app and provides api /artcicles
	 * 
	 * @param string|bool $type if param is "json" it return json with all articles - api
	 */
	public function renderOverview(string|bool $type = false) : void
	{
		$articles = $this->article->getAll();
		if($type == "json")
		{
			$json = $this->article->prepareResponseJsonAll($articles);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($json));
		}
		
		$this->template->articles = $articles;
	}

	/**
	 * New article form factory.
	 */
	protected function createComponentNewArticleForm(): Form
	{
		return $this->newArticleFactory->create(function (): void
				{
					$url = $this->link(':Frontend:Homepage:default');
					header('Location: '.$url);
				});
	}

}
