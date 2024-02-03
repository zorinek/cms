<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

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

    /** @var Nette\Database\Context */
    private $db;

    public function __construct(Nette\Database\Context $db) 
	{
        $this->db = $db;
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
	 * @return object
	 */
	public function get(int $com_id): object
	{
		$one = $this->db->table(self::TABLE_NAME)->where(self::COLUMN_COM_ID, $com_id)->fetch();
		return $one;
	}

	/**
	 * Function check if column exists in json - used for inserting from api
	 * 
	 * @param string $parameter
	 * @param object $json_obj
	 * @return string
	 */
	public function checkParameter($parameter, $json_obj) : string
	{
		if (isset($json_obj->{$parameter}))
		{
			return  $json_obj->{$parameter};
		} 
		else
		{
			throw new Exceptions\MandatoryParameterMissingException("Missing parameter " . $parameter);
		}
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
			$vals[self::COLUMN_COM_NAME] = $this->checkParameter(self::COLUMN_COM_NAME, $json_obj);
			$vals[self::COLUMN_COM_EMAIL] = $this->checkParameter(self::COLUMN_COM_EMAIL, $json_obj);
			$vals[self::COLUMN_COM_TEXT] = $this->checkParameter(self::COLUMN_COM_TEXT, $json_obj);
			$vals[self::COLUMN_ART_ID] = $this->checkParameter(self::COLUMN_ART_ID, $json_obj);
			
			$vals[self::COLUMN_COM_DATE] = date("Y-m-d H:i:s");
			$vals[self::COLUMN_COM_PARENT_ID] = isset($json_obj->{self::COLUMN_COM_PARENT_ID}) ? $json_obj->{self::COLUMN_COM_PARENT_ID} : null;
			
			$this->insert($vals);
			
			$ok = [];
			$ok["status"] = "ok";
			return $ok;
		} 
		catch (Exceptions\MandatoryParameterMissingException $e)
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
