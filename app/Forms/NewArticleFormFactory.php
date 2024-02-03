<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

final class NewArticleFormFactory
{

	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\Article */
	private $article;


	public function __construct(FormFactory $factory, Model\Article $article)
	{
		$this->factory = $factory;
		$this->article = $article;
	}

	public function create(callable $onSuccess): Form
	{
		$form = $this->factory->create();

		$form->addText($this->article::COLUMN_ART_TITLE)->setRequired();
		$form->addTextArea($this->article::COLUMN_ART_TEXT)->setRequired();
		$form->addTextArea($this->article::COLUMN_ART_CONTRIBUTOR)->setRequired();

		$form->addSubmit('insertNewArticle');

		$form->onSuccess[] = function (Form $form, \stdClass $values) use ($onSuccess): void
		{
			try
			{
				$vals = [];
				$vals[$this->article::COLUMN_ART_TITLE] = $values->{$this->article::COLUMN_ART_TITLE};
				$vals[$this->article::COLUMN_ART_TEXT] = $values->{$this->article::COLUMN_ART_TEXT};
				$vals[$this->article::COLUMN_ART_CONTRIBUTOR] = $values->{$this->article::COLUMN_ART_CONTRIBUTOR};
				$vals[$this->article::COLUMN_ART_DATE] = date("Y-m-d H:i:s");
				
				$this->article->insert($vals);
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
