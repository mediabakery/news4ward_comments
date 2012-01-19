<?php if(!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * News4ward
 * a contentelement driven news/blog-system
 *
 * @author Christoph Wiechert <wio@psitrax.de>
 * @copyright 4ward.media GbR <http://www.4wardmedia.de>
 * @package news4ward_comments
 * @filesource
 * @licence LGPL
 */

class News4wardComments extends Comments
{
	public function insertContent($strContent, $objArticle, $objModule)
	{

		// do nothing if the article do not allow comments
		if($objArticle->noComments) return $strContent;

		// fetch comment-config from the archive
		$objArchive = $this->Database->prepare('SELECT * FROM tl_news4ward WHERE id=?')->execute($objArticle->pid);

		// do nothing if the archive do not allow comments
		if($objArchive->allowComments == '') return $strContent;


		$tpl = new FrontendTemplate('news4ward_comments');
		$this->import('Comments');
		$arrNotifies = array();

		// Notify system administrator
		if ($objArchive->notify != 'notify_author')
		{
			$arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
		}

		// Notify author
		if ($objArchive->notify != 'notify_admin')
		{
			$objAuthor = $this->Database->prepare("SELECT email FROM tl_user WHERE id=?")
										->limit(1)
										->execute($objArticle->authorId);

			if ($objAuthor->numRows)
			{
				$arrNotifies[] = $objAuthor->email;
			}
		}

		$objConfig = new stdClass();

		$objConfig->perPage = $objArchive->perPage;
		$objConfig->order = $objArchive->sortOrder;
		$objConfig->template = $this->com_template;
		$objConfig->requireLogin = $objArchive->requireLogin;
		$objConfig->disableCaptcha = $objArchive->disableCaptcha;
		$objConfig->bbcode = $objArchive->bbcode;
		$objConfig->moderate = $objArchive->moderate;

		$this->Comments->addCommentsToTemplate($tpl, $objConfig, 'tl_news4ward_article', $objArticle->id, $arrNotifies);

		return $strContent.$tpl->parse();
	}
}

?>