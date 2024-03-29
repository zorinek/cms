<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use App\Exceptions;

/**
 * Article class
 */
class Article
{

	use Nette\SmartObject;

	public const
			TABLE_NAME = 'article',
			COLUMN_ART_ID = 'art_id',
			COLUMN_ART_TITLE = 'art_title',
			COLUMN_ART_TEXT = 'art_text',
			COLUMN_ART_DATE = 'art_date',
			COLUMN_ART_CONTRIBUTOR = 'art_contributor';
	
	public const
			LENGTH_ART_TITLE = 2047,
			LENGTH_ART_CONTRIBUTOR = 511;

	/** @var Nette\Database\Context */
	private $db;

	public function __construct(Nette\Database\Context $db)
	{
		$this->db = $db;
	}

	/**
	 * Function insert data for new article
	 * 
	 * @param array<mixed> $values
	 * @return int
	 */
	public function insert(array $values): int
	{
		$insert = $this->db->table(self::TABLE_NAME)->insert($values);
		return $insert->{self::COLUMN_ART_ID};
	}

	/**
	 * Function get all articles from db
	 * 
	 * @return array<Nette\Database\Table\ActiveRow>.
	 */
	public function getAll(): array
	{
		$all = $this->db->table(self::TABLE_NAME)->fetchAll();
		return $all;
	}

	/**
	 * Function get on article by its id
	 * 
	 * @param int $art_id
	 * @return object|null
	 */
	public function get(int $art_id): object|null
	{
		$one = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_ART_ID, $art_id)->fetch();
		return $one;
	}

	/**
	 * Function check if column exists in json - used for inserting from api
	 * 
	 * @param string $parameter
	 * @param object $json_obj
	 * @param int|bool $max_length
	 * @return string
	 */
	public function checkParameter($parameter, $json_obj, $max_length = false) : string
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
		
		return  $json_obj->{$parameter};
	}

	/**
	 * Function which process json for inserting new article to db - from api
	 * 
	 * @param object $json_obj
	 * @return array<string,string>
	 */
	public function processJson($json_obj) : array
	{
		try
		{
			$vals = [];
			$vals[self::COLUMN_ART_TITLE] = $this->checkParameter(self::COLUMN_ART_TITLE, $json_obj, self::LENGTH_ART_TITLE);
			$vals[self::COLUMN_ART_TEXT] = $this->checkParameter(self::COLUMN_ART_TEXT, $json_obj);
			$vals[self::COLUMN_ART_CONTRIBUTOR] = $this->checkParameter(self::COLUMN_ART_CONTRIBUTOR, $json_obj, self::LENGTH_ART_CONTRIBUTOR);
			
			$vals[self::COLUMN_ART_DATE] = date("Y-m-d H:i:s");
			
			$this->insert($vals);
			
			$ok = [];
			$ok["status"] = "ok";
			return $ok;
		} 
		catch (Exceptions\MandatoryParameterMissingException | Exceptions\EmptyParameterException | Exceptions\TooLongParameterException $e)
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
	 * Function prepares response json for returning one article via api
	 * 
	 * @param object $article
	 * @return array<string,string>
	 */
	public function prepareResponseJsonOne($article) : array
	{
		$output = [];
		$output[self::COLUMN_ART_ID] = $article->{self::COLUMN_ART_ID};
		$output[self::COLUMN_ART_TITLE] = $article->{self::COLUMN_ART_TITLE};
		$output[self::COLUMN_ART_TEXT] = $article->{self::COLUMN_ART_TEXT};
		$output[self::COLUMN_ART_DATE] = $article->{self::COLUMN_ART_DATE};
		$output[self::COLUMN_ART_CONTRIBUTOR] = $article->{self::COLUMN_ART_CONTRIBUTOR};
		return $output;
	}
	
	/**
	 * Function prepares response json for returnin all articles via api
	 * 
	 * @param array<object> $articles
	 * @return array<int|string,array<string, mixed>>
	 */
	public function prepareResponseJsonAll($articles) : array
	{
		$output = [];
		foreach($articles as $article)
		{
			$temp = [];
			$temp[self::COLUMN_ART_ID] = $article->{self::COLUMN_ART_ID};
			$temp[self::COLUMN_ART_TITLE] = $article->{self::COLUMN_ART_TITLE};
			$temp[self::COLUMN_ART_TEXT] = $article->{self::COLUMN_ART_TEXT};
			$temp[self::COLUMN_ART_DATE] = $article->{self::COLUMN_ART_DATE};
			$temp[self::COLUMN_ART_CONTRIBUTOR] = $article->{self::COLUMN_ART_CONTRIBUTOR};
			$output[$article->{self::COLUMN_ART_ID}] = $temp;
		}
		return $output;
	}

}
