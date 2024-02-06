<?php

declare(strict_types=1);

namespace App\Modules\Comment;

use App\Forms;
use App\Model;
use Nette\Application\UI\Form;

final class CommentPresenter extends \App\Presenters\BasePresenter
{

	/** @var Forms\NewCommentFormFactory $newCommentFactory @inject */
	public $newCommentFactory;
	
	/** @var Model\Comment $comment @inject */
	public $comment;

	/**
	 * Renders new comment in app and provides api /comments/new
	 * 
	 */
	public function renderNew() : void
	{
		if($this->getHttpRequest()->getHeader("content-type") !== null && $this->getHttpRequest()->getHeader("content-type") == "application/json")
		{
			$json = $this->getHttpRequest()->getRawBody();
			$json_obj = json_decode($json);
			$return = $this->comment->processJson($json_obj);
			
			if($return["status"] == "fail")
			{
				$this->getHttpResponse()->setCode(\Nette\Http\IResponse::S400_BadRequest);
			}
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($return));
		}
	}
	
	/**
	 * Renders detail of comment in app and provides api /comments/<com_id>
	 * 
	 * @param string $com_id id of comment
	 * @param string|bool $type if param is "json" it return json with one comment - api
	 */
	public function renderDetail(string $com_id, string|bool $type = false) : void
	{
		$comment = $this->comment->get((int)$com_id);
		if($type == "json")
		{
			$json = $this->comment->prepareResponseJsonOne($comment);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($json));
		}
		
		$this->template->comment = $comment;
	}
	
	/**
	 * Renders overview of all comments in app and provides api /comments
	 * 
	 * @param string|bool $type if param is "json" it return json with all comments - api
	 */
	public function renderOverview(string|bool $type = false) : void
	{
		$comments = $this->comment->getAll();
		if($type == "json")
		{
			$json = $this->comment->prepareResponseJsonAll($comments);
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse($json));
		}
		
		$this->template->comments = $comments;
	}

	/**
	 * New comment form factory.
	 */
	protected function createComponentNewCommentForm(): Form
	{
		return $this->newCommentFactory->create(function (): void
				{
					$url = $this->link(':Frontend:Homepage:default');
					header('Location: '.$url);
				});
	}

}
