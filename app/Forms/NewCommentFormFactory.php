<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class NewCommentFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Comment */
	private $comment;

	/** @var Model\Article */
	private $article;


	public function __construct(FormFactory $factory, Model\Comment $comment, Model\Article $article)
	{
		$this->factory = $factory;
		$this->comment = $comment;
		$this->article = $article;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addText($this->comment::COLUMN_COM_NAME)->setRequired();
		$form->addEmail($this->comment::COLUMN_COM_EMAIL)->setRequired();
		$form->addTextArea($this->comment::COLUMN_COM_TEXT)->setRequired();
		$articles = $this->article->getAll();
		$form->addSelect($this->comment::COLUMN_ART_ID, null, $articles)->setRequired();
		$comments = $this->comment->getAll();
		$form->addSelect($this->comment::COLUMN_COM_PARENT_ID, null, $comments)->setPrompt("Not selected");

		$form->addSubmit('insertNewComment');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$vals = [];
				$vals[$this->comment::COLUMN_COM_NAME] = $values->{$this->comment::COLUMN_COM_NAME};
				$vals[$this->comment::COLUMN_COM_EMAIL] = $values->{$this->comment::COLUMN_COM_EMAIL};
				$vals[$this->comment::COLUMN_COM_TEXT] = $values->{$this->comment::COLUMN_COM_TEXT};
				$vals[$this->comment::COLUMN_COM_DATE] = date("Y-m-d H:i:s");
				$vals[$this->comment::COLUMN_ART_ID] = $values->{$this->comment::COLUMN_ART_ID};
				$vals[$this->comment::COLUMN_COM_PARENT_ID] = isset($values->{$this->comment::COLUMN_COM_PARENT_ID}) ? $values->{$this->comment::COLUMN_ART_ID} : null;
				
				$this->comment->insert($vals);
				$onSuccess();
			} 
			catch (\Exception $e)
			{
				\Tracy\Debugger::log($e);
				$form->getPresenter()->flashMessage($e->getMessage(), 'danger');
				return;
			}

		};

		return $form;
	}

}
