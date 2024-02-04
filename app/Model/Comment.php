<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\Exceptions;
use App\Model;

/**
 * Comment class
 */
class Comment {

    use Nette\SmartObject;

    public const
            TABLE_NAME = 'comment',
            COLUMN_COM_ID = 'com_id',
            COLUMN_COM_NAME = 'com_name',
            COLUMN_COM_EMAIL = 'com_email',
            COLUMN_COM_TEXT = 'com_text',
            COLUMN_COM_DATE = 'com_date',
            COLUMN_ART_ID = 'art_id',
            COLUMN_COM_PARENT_ID = 'com_parent_id';
	
	public const
			LENGTH_COM_NAME = 511,
			LENGTH_COM_EMAIL = 255;

    /** @var Nette\Database\Context */
    private $db;
	
    /** @var Model\Article $article */
    private $article;

    public function __construct(Nette\Database\Context $db, Model\Article $article) 
	{
        $this->db = $db;
		$this->article = $article;
    }

    /**
	 * Function insert data for new comment
	 * 
	 * @param array<mixed> $values
	 * @return int
	 */
	public function insert(array $values): int
	{
		$insert = $this->db->table(self::TABLE_NAME)->insert($values);
		return $insert->{self::COLUMN_COM_ID};
	}

	/**
	 * Function get all comments from db
	 * 
	 * @return array<Nette\Database\Table\ActiveRow>.
	 */
	public function getAll(): array
	{
		$all = $this->db->table(self::TABLE_NAME)->fetchAll();
		return $all;
	}

	/**
	 * Function get one comment by its id
	 * 
	 * @param int $com_id
	 * @return object|null
	 */
	public function get(int $com_id): object|null
	{
		$one = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_COM_ID, $com_id)->fetch();
		return $one;
	}

	/**
	 * Function check if column exists in json - used for inserting from api
	 * 
	 * @param string $parameter
	 * @param object $json_obj
	 * @param int|bool $max_length
	 * @param int|bool $is_number
	 * @return string|int
	 */
	public function checkParameter($parameter, $json_obj, $max_length = false, $is_number = false) : string|int
	{
		if (!isset($json_obj->{$parameter}))
		{
			throw new Exceptions\MandatoryParameterMissingException("Missing parameter " . $parameter);
		}
		
		if(empty($json_obj->{$parameter}) || $json_obj->{$parameter} == "")
		{
			throw new Exceptions\EmptyParameterException("Empty parameter " . $parameter);
		}
		
		if($max_length !== false && mb_strlen($json_obj->{$parameter}) > $max_length)
		{
			throw new Exceptions\TooLongParameterException("Parameter " . $parameter . " is too long. Max length is " . $max_length . ". Your current length is " . mb_strlen($json_obj->{$parameter}) . ".");
		}
		
		if($is_number !== false && !is_int($json_obj->{$parameter}))
		{
			throw new Exceptions\NotNumberParameterException("Parameter " . $parameter . " is not number");
		}
		
		return  $json_obj->{$parameter};
	}

	/**
	 * Function which process json for inserting new comment to db - from api
	 * 
	 * @param object $json_obj
	 * @return array<string,string>
	 */
	public function processJson($json_obj) : array
	{
		try
		{
			$vals = [];
			$vals[self::COLUMN_COM_NAME] = $this->checkParameter(self::COLUMN_COM_NAME, $json_obj, self::LENGTH_COM_NAME);
			$vals[self::COLUMN_COM_EMAIL] = $this->checkParameter(self::COLUMN_COM_EMAIL, $json_obj, self::LENGTH_COM_EMAIL);
			$vals[self::COLUMN_COM_TEXT] = $this->checkParameter(self::COLUMN_COM_TEXT, $json_obj);
			
			$check_article_id = $this->checkParameter(self::COLUMN_ART_ID, $json_obj, false, true);
			$exists_article = $this->article->get($check_article_id);
			if(!$exists_article)
			{
				throw new Exceptions\ArticleNotExistsException("Article with number " . $check_article_id . " does not exists");
			}
			$vals[self::COLUMN_ART_ID] = $check_article_id;
			
			$vals[self::COLUMN_COM_DATE] = date("Y-m-d H:i:s");
			
			$check_parent_id = $this->checkParameter(self::COLUMN_COM_PARENT_ID, $json_obj, false, true);
			$exists_parent_comment = $this->get($check_parent_id);
			if(!$exists_parent_comment)
			{
				throw new Exceptions\ParentCommentNotExistsException("Parent comment with number " . $check_parent_id . " does not exists");
			}
			$vals[self::COLUMN_COM_PARENT_ID] = $check_parent_id;
			
			$this->insert($vals);
			
			$ok = [];
			$ok["status"] = "ok";
			return $ok;
		} 
		catch 
			(
				Exceptions\MandatoryParameterMissingException | 
				Exceptions\EmptyParameterException | 
				Exceptions\TooLongParameterException | 
				Exceptions\NotNumberParameterException | 
				Exceptions\ArticleNotExistsException | 
				Exceptions\ParentCommentNotExistsException $e
			)
		{
			\Tracy\Debugger::log($e);
			$error = [];
			$error["status"] = "fail";
			$error["error"] = $e->getMessage();
			return $error;
		} 
		catch (\Exception $e)
		{
			\Tracy\Debugger::log($e);
			$error = [];
			$error["status"] = "fail";
			$error["error"] = $e->getMessage();
			return $error;
		}
	}
	
	/**
	 * Function prepares response json for returning one comment via api
	 * 
	 * @param object $comment
	 * @return array<string,string>
	 */
	public function prepareResponseJsonOne($comment) : array
	{
		$output = [];
		$output[self::COLUMN_COM_ID] = $comment->{self::COLUMN_COM_ID};
		$output[self::COLUMN_COM_NAME] = $comment->{self::COLUMN_COM_NAME};
		$output[self::COLUMN_COM_EMAIL] = $comment->{self::COLUMN_COM_EMAIL};
		$output[self::COLUMN_COM_TEXT] = $comment->{self::COLUMN_COM_TEXT};
		$output[self::COLUMN_COM_DATE] = $comment->{self::COLUMN_COM_DATE};
		$output[self::COLUMN_ART_ID] = $comment->{self::COLUMN_ART_ID};
		$output[self::COLUMN_COM_PARENT_ID] = $comment->{self::COLUMN_COM_PARENT_ID};
		return $output;
	}
	
	/**
	 * Function prepares response json for returning all comments via api
	 * 
	 * @param array<object> $comments
	 * @return array<int|string,array<string, mixed>>
	 */
	public function prepareResponseJsonAll($comments) : array
	{
		$output = [];
		foreach($comments as $comment)
		{
			$temp = [];
			$temp[self::COLUMN_COM_ID] = $comment->{self::COLUMN_COM_ID};
			$temp[self::COLUMN_COM_NAME] = $comment->{self::COLUMN_COM_NAME};
			$temp[self::COLUMN_COM_EMAIL] = $comment->{self::COLUMN_COM_EMAIL};
			$temp[self::COLUMN_COM_TEXT] = $comment->{self::COLUMN_COM_TEXT};
			$temp[self::COLUMN_COM_DATE] = $comment->{self::COLUMN_COM_DATE};
			$temp[self::COLUMN_ART_ID] = $comment->{self::COLUMN_ART_ID};
			$temp[self::COLUMN_COM_PARENT_ID] = $comment->{self::COLUMN_COM_PARENT_ID};
			$output[$comment->{self::COLUMN_COM_ID}] = $temp;
		}
		return $output;
	}

}
