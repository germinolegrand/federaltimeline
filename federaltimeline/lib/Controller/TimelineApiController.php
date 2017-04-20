<?php
namespace OCA\FederalTimeline\Controller;

use \DateTime;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\ApiController;

class TimelineApiController extends ApiController {
	private $userId;
	private $userFolder;
	private $systemTagManager;
	private $systemTagObjectMapper;

	public function __construct($AppName, IRequest $request, $UserId, $ServerContainer){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->userFolder = $ServerContainer->getUserFolder();
		$this->systemTagManager = $ServerContainer->getSystemTagManager();
		$this->systemTagObjectMapper = $ServerContainer->getSystemTagObjectMapper();
	}

	/**
	 * 
	 */
	public function index() {
		$tlTag = $this->systemTagManager->getTag('timeline', true, true);
		if(!$tlTag){
			return new JSONResponse([], \OCP\AppFramework\Http::STATUS_NO_CONTENT);
		}
		$tlobjects = $this->systemTagObjectMapper->getObjectIdsForTags($tlTag->getId(), 'files');
		$tagIds = $this->systemTagObjectMapper->getTagIdsForObjects($tlobjects, 'files');
		$dateinstances = [];
		foreach ($tlobjects as $tlo) {
			$di = [];
			foreach ($this->systemTagManager->getTagsByIds($tagIds[$tlo]) as $tag) {
				$diData = [];
				if(preg_match('/^tl:(.*)/', $tag->getName(), $diData)){
					if(preg_match('#^../../....$#', $diData[1])){
						$dpff = date_create_from_format('!d/m/Y', $diData[1]);
						$di['di_date'] = date_timestamp_get($dpff);
					} else {
						$di['di_instance'] = $diData[1];
					}
				}
				$di['tags'][] = $tag->getName();
			}
			foreach ($this->userFolder->getById($tlo) as $file) {
				$di['id'] = $file->getId();
				$di['name'] = $file->getName();
				$di['mimetype'] = $file->getMimetype();
				$di['ddl'] = $file->getStorage()->getDirectDownload($file->getInternalPath());
			}
			$dateinstances[] = $di;
		}
		return new JSONResponse($dateinstances, count($dateinstances) > 0 ? \OCP\AppFramework\Http::STATUS_OK : \OCP\AppFramework\Http::STATUS_NO_CONTENT);
	}

	/**
	 * @param $date
	 * @param $instance
	 * @param $name
	 */
	public function uploadFile($date, $instance, $name)
	{
		$upFile = $this->request->getUploadedFile('file0');
		$name = $this->userFolder->getNonExistingName($name);
		$nfile = $this->userFolder->newFile($name);
		$nfile->putContent(file_get_contents($upFile['tmp_name']));
		$date = date('d/m/Y', $date);

		$tagIds = [];
		try{
			$tagIds['timeline'] = $this->systemTagManager->getTag('timeline', true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['timeline'] = $this->systemTagManager->createTag('timeline', true, true)->getId();
		}
		try{
			$tagIds['tl:date'] = $this->systemTagManager->getTag('tl:'.$date, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:date'] = $this->systemTagManager->createTag('tl:'.$date, true, true)->getId();
		}
		try{
			$tagIds['tl:instance'] = $this->systemTagManager->getTag('tl:'.$instance, true, true)->getId();
		} catch(\OCP\SystemTag\TagNotFoundException $e) {
			$tagIds['tl:instance'] = $this->systemTagManager->createTag('tl:'.$instance, true, true)->getId();
		}
		$this->systemTagObjectMapper->assignTags($nfile->getId(), 'files', $tagIds);

		return [
			'id' => $nfile->getId()
		];
	}

	/**
	 * @param int $fileId
	 */
	public function downloadFile($fileId)
	{
		$files = $this->userFolder->getById($fileId);
		//TODO check permissions
		if(count($files)){
			return new DataDownloadResponse($files[0]->getContent(), $files[0]->getName(), $files[0]->getMimetype());
		}

		return new JSONResponse("", \OCP\AppFramework\Http::STATUS_NOT_FOUND);
	}

}
