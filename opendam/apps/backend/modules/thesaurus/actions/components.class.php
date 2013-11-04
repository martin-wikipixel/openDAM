<?php

class thesaurusComponents extends sfComponents
{
	public function executeRandomTags()
	{
		$tags = Array();

		$connection = Propel::getConnection();

		$query = "	SELECT *
					FROM
					(
						SELECT DISTINCT tag.*
						FROM tag, file_tag
						WHERE tag.id = file_tag.tag_id
						AND tag.customer_id = ".$this->getUser()->getCustomerId()."
						AND tag.title NOT IN (	SELECT thesaurus.title
												FROM thesaurus
												WHERE thesaurus.customer_id = ".$this->getUser()->getCustomerId()."
						)
						ORDER BY RAND()
						LIMIT 0, 50
					) Q1
					ORDER BY title ASC";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while($rs = $statement->fetch())
		{
			$tag = new Tag();
			$tag->hydrate($rs);
			$tags[] = $tag;
		}

		$statement->closeCursor();
		$statement = null;

		$this->tags = $tags;
	}
}